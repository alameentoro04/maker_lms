<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignmentRequest;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;

class AssignmentController extends Controller
{
    /** Create-or-update — the curriculum builder posts here whenever the "assignment" lesson type's config panel is saved. */
    public function upsert(AssignmentRequest $request, Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);
        abort_unless($lesson->type === 'assignment', 422, 'This lesson is not an assignment.');

        Assignment::query()->updateOrCreate(
            ['lesson_id' => $lesson->id],
            $request->validated()
        );

        return back()->with('status', 'Assignment settings saved.');
    }
}
