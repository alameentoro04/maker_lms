<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitAssignmentRequest;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Services\CourseAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function submit(SubmitAssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        $lesson = $assignment->lesson;
        $course = $lesson->module->course;
        $user = $request->user();

        abort_unless($this->access->hasAccessToLesson($user, $lesson), 403, 'You are not enrolled in this course.');

        $enrollment = $this->access->activeEnrollmentFor($user, $course);
        abort_unless($enrollment, 403);

        $existing = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->latest('attempt_number')
            ->first();

        if ($existing && ! $assignment->allow_resubmission) {
            abort(422, 'This assignment does not allow resubmission — you already have a submission on file.');
        }

        $validated = $request->validated();
        $filePath = null;
        $fileOriginalName = null;

        if ($request->hasFile('file')) {
            // Stored on the PRIVATE disk — never public — served later only
            // through an authorized download route (see routes/web.php).
            $filePath = $request->file('file')->store('assignment-submissions', 'local');
            $fileOriginalName = $request->file('file')->getClientOriginalName();
        }

        AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'enrollment_id' => $enrollment->id,
            'attempt_number' => $existing ? $existing->attempt_number + 1 : 1,
            'text_response' => $validated['text_response'] ?? null,
            'external_link' => $validated['external_link'] ?? null,
            'file_path' => $filePath,
            'file_original_name' => $fileOriginalName,
            'status' => 'submitted',
        ]);

        return back()->with('status', 'Assignment submitted.');
    }

    public function downloadSubmissionFile(AssignmentSubmission $submission): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = request()->user();
        $course = $submission->assignment->lesson->module->course;

        $isOwner = $submission->user_id === $user->id;
        $canGrade = $user->hasPermission('courses.manage') || $course->instructors()->where('users.id', $user->id)->exists();

        abort_unless($isOwner || $canGrade, 403);
        abort_unless($submission->file_path, 404);

        return Storage::disk('local')->download($submission->file_path, $submission->file_original_name);
    }
}
