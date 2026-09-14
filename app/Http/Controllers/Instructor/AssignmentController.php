<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    /** Every assignment across the instructor's own courses, for a single grading queue. */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $courseIds = $user->hasPermission('courses.manage')
            ? Course::query()->pluck('id')
            : $user->instructorCourses()->pluck('courses.id');

        $assignments = Assignment::query()
            ->whereHas('lesson.module.course', fn ($q) => $q->whereIn('id', $courseIds))
            ->with(['lesson.module.course', 'submissions'])
            ->get()
            ->map(fn (Assignment $a) => [
                'id' => $a->id,
                'lesson_title' => $a->lesson->title,
                'course_title' => $a->lesson->module->course->title,
                'due_at' => $a->due_at?->toFormattedDateString(),
                'submissions_count' => $a->submissions->count(),
                'ungraded_count' => $a->submissions->where('status', 'submitted')->count(),
            ]);

        return Inertia::render('Instructor/Assignments/Index', ['assignments' => $assignments]);
    }
}
