<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ProjectShowcase;
use App\Models\Testimonial;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $featuredCourse = Course::query()->published()->with('cohorts')->first();

        $nextCohort = $featuredCourse
            ? $featuredCourse->cohorts()->whereIn('status', ['upcoming', 'enrollment_open'])->orderBy('start_date')->first()
            : null;

        return Inertia::render('Public/Home', [
            'featuredCourse' => $featuredCourse ? [
                'title' => $featuredCourse->title,
                'slug' => $featuredCourse->slug,
                'summary' => $featuredCourse->summary,
                'level' => $featuredCourse->level,
                'duration_weeks' => $featuredCourse->duration_weeks,
                'price' => $featuredCourse->formattedPrice(),
            ] : null,
            'nextCohort' => $nextCohort ? [
                'name' => $nextCohort->name,
                'slug' => $nextCohort->slug,
                'start_date' => $nextCohort->start_date->toFormattedDateString(),
                'status' => $nextCohort->statusLabel(),
                'capacity' => $nextCohort->capacity,
            ] : null,
            'testimonials' => Testimonial::query()->published()->limit(3)->get(
                ['author_name', 'author_role', 'quote']
            ),
            'showcaseItems' => ProjectShowcase::query()->published()->limit(4)->get(
                ['title', 'author_name', 'image_path']
            ),
        ]);
    }
}
