<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $enrollments = Enrollment::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['active', 'completed'])
            ->with(['course.modules.lessons', 'cohort'])
            ->get();

        $cards = $enrollments->map(function (Enrollment $enrollment) use ($request) {
            $lessons = $enrollment->course->modules->flatMap->lessons->where('is_published', true)->values();

            $completedLessonIds = LessonProgress::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('lesson_id', $lessons->pluck('id'))
                ->whereNotNull('completed_at')
                ->pluck('lesson_id');

            $nextLesson = $lessons->first(fn ($lesson) => ! $completedLessonIds->contains($lesson->id));

            return [
                'course_title' => $enrollment->course->title,
                'course_slug' => $enrollment->course->slug,
                'cohort_name' => $enrollment->cohort?->name,
                'status' => $enrollment->status,
                'progress_percent' => $lessons->count() > 0 ? (int) round(($completedLessonIds->count() / $lessons->count()) * 100) : 0,
                'total_lessons' => $lessons->count(),
                'completed_lessons' => $completedLessonIds->count(),
                'next_lesson_title' => $nextLesson?->title,
            ];
        });

        return Inertia::render('Student/Dashboard', [
            'enrollments' => $cards,
        ]);
    }
}
