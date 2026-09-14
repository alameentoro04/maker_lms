<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LessonRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;

class LessonController extends Controller
{
    public function store(LessonRequest $request, Course $course, Module $module): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($module->course_id === $course->id, 404);

        $module->lessons()->create($request->validated());

        return back()->with('status', 'Lesson added.');
    }

    public function update(LessonRequest $request, Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);

        $lesson->update($request->validated());

        return back()->with('status', 'Lesson updated.');
    }

    public function destroy(Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);

        $lesson->delete();

        return back()->with('status', 'Lesson removed.');
    }
}
