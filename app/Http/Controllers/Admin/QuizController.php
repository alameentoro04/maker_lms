<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuizQuestionRequest;
use App\Http\Requests\Admin\QuizRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\RedirectResponse;

class QuizController extends Controller
{
    public function upsert(QuizRequest $request, Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);
        abort_unless($lesson->type === 'quiz', 422, 'This lesson is not a quiz.');

        Quiz::query()->updateOrCreate(['lesson_id' => $lesson->id], $request->validated());

        return back()->with('status', 'Quiz settings saved.');
    }

    public function storeQuestion(QuizQuestionRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz->lesson->module->course);

        $validated = $request->validated();

        $question = $quiz->questions()->create([
            'type' => $validated['type'],
            'question' => $validated['question'],
            'explanation' => $validated['explanation'] ?? null,
            'points' => $validated['points'],
            'order' => $quiz->questions()->count(),
        ]);

        if ($validated['type'] !== 'short_answer') {
            foreach ($validated['options'] as $i => $option) {
                $question->options()->create([
                    'option_text' => $option['option_text'],
                    'is_correct' => (bool) ($option['is_correct'] ?? false),
                    'order' => $i,
                ]);
            }
        }

        return back()->with('status', 'Question added.');
    }

    public function destroyQuestion(Quiz $quiz, QuizQuestion $question): RedirectResponse
    {
        $this->authorize('update', $quiz->lesson->module->course);
        abort_unless($question->quiz_id === $quiz->id, 404);

        $question->delete();

        return back()->with('status', 'Question removed.');
    }
}
