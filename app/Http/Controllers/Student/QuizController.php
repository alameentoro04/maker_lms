<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Services\CourseAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function start(Request $request, Quiz $quiz): RedirectResponse
    {
        $lesson = $quiz->lesson;
        $user = $request->user();

        abort_unless($this->access->hasAccessToLesson($user, $lesson), 403);

        $enrollment = $this->access->activeEnrollmentFor($user, $lesson->module->course);
        abort_unless($enrollment, 403, 'You must be enrolled to take this quiz.');

        $attemptCount = QuizAttempt::query()->where('quiz_id', $quiz->id)->where('user_id', $user->id)->count();
        $inProgress = QuizAttempt::query()->where('quiz_id', $quiz->id)->where('user_id', $user->id)->whereNull('submitted_at')->first();

        if (! $inProgress) {
            abort_if($attemptCount >= $quiz->attempt_limit, 422, 'You have used all your attempts for this quiz.');

            QuizAttempt::query()->create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'attempt_number' => $attemptCount + 1,
                'started_at' => now(),
            ]);
        }

        return back();
    }

    public function submit(Request $request, QuizAttempt $attempt): RedirectResponse
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);
        abort_if($attempt->isSubmitted(), 422, 'This attempt was already submitted.');

        $quiz = $attempt->quiz()->with('questions.options')->first();

        $answers = $request->input('answers', []); // [question_id => option_id|text]
        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points;
            $given = $answers[$question->id] ?? null;

            if ($question->type === 'short_answer') {
                // NOT auto-graded — see README. Recorded for an instructor to review manually (not built yet).
                QuizAnswer::query()->create([
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $question->id,
                    'text_answer' => is_string($given) ? $given : null,
                    'is_correct' => null,
                ]);

                continue;
            }

            $selectedOption = $given ? QuizOption::query()->find($given) : null;
            $isCorrect = $selectedOption?->is_correct === true && $selectedOption->quiz_question_id === $question->id;

            if ($isCorrect) {
                $earnedPoints += $question->points;
            }

            QuizAnswer::query()->create([
                'quiz_attempt_id' => $attempt->id,
                'quiz_question_id' => $question->id,
                'selected_option_id' => $selectedOption?->id,
                'is_correct' => $isCorrect,
            ]);
        }

        // Short-answer points are excluded from both sides of the ratio since
        // they can't be auto-graded — scoring only reflects auto-gradable questions.
        $autoGradableTotal = $quiz->questions->where('type', '!=', 'short_answer')->sum('points');
        $score = $autoGradableTotal > 0 ? (int) round(($earnedPoints / $autoGradableTotal) * 100) : 0;

        $attempt->update([
            'submitted_at' => now(),
            'score' => $score,
            'passed' => $score >= $quiz->passing_score,
        ]);

        return back()->with('status', $attempt->passed ? "Quiz passed — {$score}%" : "Quiz submitted — {$score}% (passing score: {$quiz->passing_score}%)");
    }
}
