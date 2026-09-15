<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\CourseAccessService;
use App\Services\Video\VideoProviderInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearnController extends Controller
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function show(Request $request, Course $course): Response
    {
        abort_unless($this->access->hasAccessToCourse($request->user(), $course), 403, 'You are not enrolled in this course.');

        $course->load(['modules.lessons' => fn ($q) => $q->where('is_published', true)]);

        $completedLessonIds = LessonProgress::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('lesson_id', $course->modules->flatMap->lessons->pluck('id'))
            ->whereNotNull('completed_at')
            ->pluck('lesson_id');

        return Inertia::render('Student/Learn/Show', [
            'course' => ['title' => $course->title, 'slug' => $course->slug],
            'modules' => $course->modules->map(fn ($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'lessons' => $m->lessons->map(fn ($l) => [
                    'id' => $l->id,
                    'title' => $l->title,
                    'type' => $l->type,
                    'completed' => $completedLessonIds->contains($l->id),
                ]),
            ]),
        ]);
    }

    public function lesson(Request $request, Course $course, Lesson $lesson, VideoProviderInterface $video): Response
    {
        abort_unless($lesson->module->course_id === $course->id, 404);
        abort_unless($this->access->hasAccessToLesson($request->user(), $lesson), 403, 'You do not have access to this lesson.');
        abort_unless($lesson->is_published, 404);

        $course->load(['modules.lessons' => fn ($q) => $q->where('is_published', true)]);

        $allLessons = $course->modules->flatMap->lessons->values();
        $currentIndex = $allLessons->search(fn ($l) => $l->id === $lesson->id);

        $completedLessonIds = LessonProgress::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('lesson_id', $allLessons->pluck('id'))
            ->whereNotNull('completed_at')
            ->pluck('lesson_id');

        $lesson->load('resources', 'assignment', 'quiz.questions.options');

        $mySubmission = null;
        if ($lesson->assignment) {
            $latest = AssignmentSubmission::query()
                ->where('assignment_id', $lesson->assignment->id)
                ->where('user_id', $request->user()->id)
                ->latest('attempt_number')
                ->first();

            $mySubmission = $latest ? [
                'id' => $latest->id,
                'status' => $latest->status,
                'grade' => $latest->grade,
                'instructor_feedback' => $latest->instructor_feedback,
                'submitted_at' => $latest->created_at->toDayDateTimeString(),
                'has_file' => (bool) $latest->file_path,
                'attempt_number' => $latest->attempt_number,
            ] : null;
        }

        $quizData = null;
        if ($lesson->quiz) {
            $quizData = $this->buildQuizPayload($lesson->quiz, $request->user()->id);
        }

        return Inertia::render('Student/Learn/Lesson', [
            'course' => ['title' => $course->title, 'slug' => $course->slug],
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'type' => $lesson->type,
                'content' => $lesson->content,
                'completed' => $completedLessonIds->contains($lesson->id),
                'video_stream_url' => $lesson->type === 'video' && $lesson->video_reference
                    ? $video->generateSignedPlaybackUrl($lesson->video_reference)
                    : null,
                'resources' => $lesson->resources->map(fn ($r) => ['title' => $r->title, 'url' => $r->url]),
                'assignment' => $lesson->assignment ? [
                    'id' => $lesson->assignment->id,
                    'instructions' => $lesson->assignment->instructions,
                    'due_at' => $lesson->assignment->due_at?->toDayDateTimeString(),
                    'past_due' => $lesson->assignment->isPastDue(),
                    'allow_resubmission' => $lesson->assignment->allow_resubmission,
                    'my_submission' => $mySubmission,
                ] : null,
                'quiz' => $quizData,
            ],
            'navigation' => [
                'modules' => $course->modules->map(fn ($m) => [
                    'title' => $m->title,
                    'lessons' => $m->lessons->map(fn ($l) => [
                        'id' => $l->id,
                        'title' => $l->title,
                        'completed' => $completedLessonIds->contains($l->id),
                        'is_current' => $l->id === $lesson->id,
                    ]),
                ]),
                'previous_lesson_id' => $currentIndex > 0 ? $allLessons[$currentIndex - 1]->id : null,
                'next_lesson_id' => $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1]->id : null,
            ],
        ]);
    }

    /**
     * Never sends question content to the browser until the student has an
     * in-progress attempt — and never sends `is_correct` on options at all,
     * even then. Grading happens entirely server-side in QuizController.
     */
    private function buildQuizPayload(Quiz $quiz, int $userId): array
    {
        $attempts = QuizAttempt::query()->where('quiz_id', $quiz->id)->where('user_id', $userId)->orderByDesc('attempt_number')->get();
        $inProgress = $attempts->firstWhere('submitted_at', null);
        $latestSubmitted = $attempts->whereNotNull('submitted_at')->first();

        return [
            'id' => $quiz->id,
            'passing_score' => $quiz->passing_score,
            'time_limit_minutes' => $quiz->time_limit_minutes,
            'attempt_limit' => $quiz->attempt_limit,
            'attempts_used' => $attempts->count(),
            'attempts_remaining' => max(0, $quiz->attempt_limit - $attempts->count()),
            'in_progress_attempt' => $inProgress ? [
                'id' => $inProgress->id,
                'started_at' => $inProgress->started_at->toIso8601String(),
                'questions' => $quiz->questions->map(fn ($q) => [
                    'id' => $q->id,
                    'type' => $q->type,
                    'question' => $q->question,
                    'options' => $q->options->map(fn ($o) => ['id' => $o->id, 'option_text' => $o->option_text]),
                ]),
            ] : null,
            'latest_result' => (! $inProgress && $latestSubmitted) ? [
                'score' => $latestSubmitted->score,
                'passed' => $latestSubmitted->passed,
                'submitted_at' => $latestSubmitted->submitted_at->toDayDateTimeString(),
            ] : null,
        ];
    }

    /**
     * Server-side lesson completion. The frontend detects a video ending or a
     * "mark complete" click and calls this — but access is re-verified here
     * regardless of what the frontend claims, per spec principle: "Do not
     * rely exclusively on frontend state."
     */
    public function completeLesson(Request $request, Lesson $lesson): RedirectResponse
    {
        abort_unless($this->access->hasAccessToLesson($request->user(), $lesson), 403);

        $course = $lesson->module->course;
        $enrollment = $this->access->activeEnrollmentFor($request->user(), $course);

        abort_unless($enrollment, 403, 'You must be enrolled to track progress on this lesson.');

        LessonProgress::query()->updateOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['enrollment_id' => $enrollment->id, 'completed_at' => now()]
        );

        return back()->with('status', 'Lesson marked complete.');
    }
}
