<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExamQuestionRequest;
use App\Http\Requests\Admin\ExamRequest;
use App\Models\Cohort;
use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExamController extends Controller
{
    public function edit(Cohort $cohort): Response
    {
        $this->authorize('update', $cohort);

        $exam = $cohort->exam()->with('questions')->first();

        return Inertia::render('Admin/Exams/Edit', [
            'cohort' => ['id' => $cohort->id, 'name' => $cohort->name],
            'exam' => $exam ? [
                'id' => $exam->id,
                'title' => $exam->title,
                'instructions' => $exam->instructions,
                'passing_score' => $exam->passing_score,
                'time_limit_minutes' => $exam->time_limit_minutes,
                'attempt_limit' => $exam->attempt_limit,
                'randomize_questions' => $exam->randomize_questions,
                'questions' => $exam->questions->map(fn (ExamQuestion $q) => [
                    'id' => $q->id,
                    'type' => $q->type,
                    'question' => $q->question,
                    'options' => $q->options,
                    'points' => $q->points,
                ]),
            ] : null,
        ]);
    }

    public function upsert(ExamRequest $request, Cohort $cohort): RedirectResponse
    {
        $this->authorize('update', $cohort);

        Exam::query()->updateOrCreate(['cohort_id' => $cohort->id], $request->validated());

        return back()->with('status', 'Exam settings saved.');
    }

    public function storeQuestion(ExamQuestionRequest $request, Exam $exam): RedirectResponse
    {
        $this->authorize('update', $exam->cohort);

        $validated = $request->validated();

        $exam->questions()->create([
            'type' => $validated['type'],
            'question' => $validated['question'],
            'options' => $validated['options'],
            'explanation' => $validated['explanation'] ?? null,
            'points' => $validated['points'],
            'order' => $exam->questions()->count(),
        ]);

        return back()->with('status', 'Question added.');
    }

    public function destroyQuestion(Exam $exam, ExamQuestion $question): RedirectResponse
    {
        $this->authorize('update', $exam->cohort);
        abort_unless($question->exam_id === $exam->id, 404);

        $question->delete();

        return back()->with('status', 'Question removed.');
    }
}
