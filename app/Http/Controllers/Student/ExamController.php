<?php

namespace App\Http\Controllers\Student;

use App\Actions\IssueCertificateAction;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Services\CourseAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExamController extends Controller
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function show(Request $request, Course $course): Response
    {
        $user = $request->user();
        abort_unless($this->access->hasAccessToCourse($user, $course), 403);

        $enrollment = $this->access->activeEnrollmentFor($user, $course);
        abort_unless($enrollment && $enrollment->cohort_id, 404, 'No cohort enrollment found for this course.');

        $exam = $enrollment->cohort->exam()->with('questions')->first();

        abort_unless($exam, 404, 'No exam has been configured for this cohort yet.');

        $attempts = ExamAttempt::query()->where('exam_id', $exam->id)->where('user_id', $user->id)->orderByDesc('attempt_number')->get();
        $inProgress = $attempts->firstWhere('submitted_at', null);
        $latestSubmitted = $attempts->whereNotNull('submitted_at')->first();

        $certificate = Certificate::query()->where('enrollment_id', $enrollment->id)->where('status', 'active')->first();

        return Inertia::render('Student/Exam/Show', [
            'course' => ['title' => $course->title, 'slug' => $course->slug],
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'instructions' => $exam->instructions,
                'passing_score' => $exam->effectivePassingScore(),
                'time_limit_minutes' => $exam->time_limit_minutes,
                'attempt_limit' => $exam->attempt_limit,
                'attempts_used' => $attempts->count(),
                'attempts_remaining' => max(0, $exam->attempt_limit - $attempts->count()),
                'in_progress_attempt' => $inProgress ? [
                    'id' => $inProgress->id,
                    'started_at' => $inProgress->started_at->toIso8601String(),
                    'questions' => $exam->questions->map(fn ($q) => [
                        'id' => $q->id,
                        'type' => $q->type,
                        'question' => $q->question,
                        'options' => $q->optionsForStudent(),
                    ]),
                ] : null,
                'latest_result' => (! $inProgress && $latestSubmitted) ? [
                    'score' => $latestSubmitted->score,
                    'passed' => $latestSubmitted->passed,
                    'submitted_at' => $latestSubmitted->submitted_at->toDayDateTimeString(),
                ] : null,
            ],
            'certificate' => $certificate ? ['certificate_id' => $certificate->certificate_id] : null,
        ]);
    }

    public function start(Request $request, Course $course): RedirectResponse
    {
        $user = $request->user();
        $enrollment = $this->access->activeEnrollmentFor($user, $course);
        abort_unless($enrollment && $enrollment->cohort_id, 404);

        $exam = $enrollment->cohort->exam;
        abort_unless($exam, 404);

        $attemptCount = ExamAttempt::query()->where('exam_id', $exam->id)->where('user_id', $user->id)->count();
        $inProgress = ExamAttempt::query()->where('exam_id', $exam->id)->where('user_id', $user->id)->whereNull('submitted_at')->first();

        if (! $inProgress) {
            abort_if($attemptCount >= $exam->attempt_limit, 422, 'You have used all your attempts for this exam.');

            ExamAttempt::query()->create([
                'exam_id' => $exam->id,
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'attempt_number' => $attemptCount + 1,
                'started_at' => now(),
            ]);
        }

        return to_route('learn.exam.show', $course);
    }

    public function submit(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);
        abort_if($attempt->isSubmitted(), 422, 'This attempt was already submitted.');

        $exam = $attempt->exam()->with('questions')->first();
        $answers = $request->input('answers', []); // [question_id => option_index]

        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($exam->questions as $question) {
            $totalPoints += $question->points;
            $selectedIndex = array_key_exists((string) $question->id, $answers) ? (int) $answers[$question->id] : null;
            $isCorrect = $selectedIndex !== null && $selectedIndex === $question->correctOptionIndex();

            if ($isCorrect) {
                $earnedPoints += $question->points;
            }

            ExamAnswer::query()->create([
                'exam_attempt_id' => $attempt->id,
                'exam_question_id' => $question->id,
                'selected_option_index' => $selectedIndex,
                'is_correct' => $isCorrect,
            ]);
        }

        $score = $totalPoints > 0 ? (int) round(($earnedPoints / $totalPoints) * 100) : 0;
        $passed = $score >= $exam->effectivePassingScore();

        $attempt->update(['submitted_at' => now(), 'score' => $score, 'passed' => $passed]);

        if ($passed) {
            (new IssueCertificateAction())->execute($attempt->enrollment);
        }

        return back()->with('status', $passed
            ? "You passed with {$score}%! Your certificate is ready."
            : "Score: {$score}% — passing score is {$exam->effectivePassingScore()}%."
        );
    }
}
