<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(): Response
    {
        $courses = Course::query()
            ->published()
            ->with('category')
            ->get()
            ->map(fn (Course $course) => [
                'title' => $course->title,
                'slug' => $course->slug,
                'summary' => $course->summary,
                'level' => $course->level,
                'duration_weeks' => $course->duration_weeks,
                'price' => $course->formattedPrice(),
                'category' => $course->category?->name,
            ]);

        return Inertia::render('Public/Courses/Index', [
            'courses' => $courses,
            'categories' => Category::query()->orderBy('order')->get(['name', 'slug']),
        ]);
    }

    public function show(Course $course): Response
    {
        abort_unless($course->status === 'published', 404);

        $course->load(['instructors.profile', 'cohorts' => fn ($q) => $q->orderBy('start_date')]);

        return Inertia::render('Public/Courses/Show', [
            'course' => [
                'title' => $course->title,
                'slug' => $course->slug,
                'summary' => $course->summary,
                'description' => $course->description,
                'objectives' => $course->objectives ?? [],
                'requirements' => $course->requirements ?? [],
                'level' => $course->level,
                'duration_weeks' => $course->duration_weeks,
                'price' => $course->formattedPrice(),
            ],
            'instructors' => $course->instructors->map(fn ($i) => [
                'name' => $i->name,
                'headline' => $i->profile?->headline,
                'id' => $i->id,
            ]),
            'cohorts' => $course->cohorts->map(fn ($c) => [
                'name' => $c->name,
                'slug' => $c->slug,
                'start_date' => $c->start_date->toFormattedDateString(),
                'end_date' => $c->end_date->toFormattedDateString(),
                'status' => $c->statusLabel(),
                'accepting_enrollment' => $c->isAcceptingEnrollment(),
                'capacity' => $c->capacity,
            ]),
        ]);
    }
}
