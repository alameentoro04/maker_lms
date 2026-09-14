<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\GradeSubmissionRequest;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function index(Assignment $assignment): Response
    {
        $this->authorizeAccess($assignment);

        $assignment->load(['submissions.user', 'lesson.module.course']);

        return Inertia::render('Instructor/Assignments/Submissions', [
            'assignment' => [
                'id' => $assignment->id,
                'lesson_title' => $assignment->lesson->title,
                'course_title' => $assignment->lesson->module->course->title,
                'instructions' => $assignment->instructions,
            ],
            'submissions' => $assignment->submissions->sortByDesc('created_at')->values()->map(fn (AssignmentSubmission $s) => [
                'id' => $s->id,
                'student' => $s->user->name,
                'submitted_at' => $s->created_at->toDayDateTimeString(),
                'text_response' => $s->text_response,
                'external_link' => $s->external_link,
                'has_file' => (bool) $s->file_path,
                'status' => $s->status,
                'grade' => $s->grade,
                'instructor_feedback' => $s->instructor_feedback,
            ]),
        ]);
    }

    public function grade(GradeSubmissionRequest $request, AssignmentSubmission $submission): RedirectResponse
    {
        $this->authorizeAccess($submission->assignment);

        $submission->update([
            ...$request->validated(),
            'status' => 'graded',
            'graded_by' => $request->user()->id,
            'graded_at' => now(),
        ]);

        return back()->with('status', 'Submission graded.');
    }

    private function authorizeAccess(Assignment $assignment): void
    {
        $user = request()->user();
        $course = $assignment->lesson->module->course;

        abort_unless(
            $user->hasPermission('courses.manage') || $course->instructors()->where('users.id', $user->id)->exists(),
            403
        );
    }
}
