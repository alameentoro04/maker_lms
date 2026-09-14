<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ModuleRequest;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;

/**
 * Modules/lessons are managed inline from the course edit screen (the
 * "curriculum builder" section of Admin/Courses/Form.tsx) rather than
 * getting their own index/show pages — there's nothing to browse
 * independently of the course they belong to.
 */
class ModuleController extends Controller
{
    public function store(ModuleRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $course->modules()->create($request->validated());

        return back()->with('status', 'Module added.');
    }

    public function update(ModuleRequest $request, Course $course, Module $module): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($module->course_id === $course->id, 404);

        $module->update($request->validated());

        return back()->with('status', 'Module updated.');
    }

    public function destroy(Course $course, Module $module): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($module->course_id === $course->id, 404);

        $module->delete();

        return back()->with('status', 'Module removed.');
    }
}
