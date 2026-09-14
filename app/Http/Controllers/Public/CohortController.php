<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use Inertia\Inertia;
use Inertia\Response;

class CohortController extends Controller
{
    public function show(Cohort $cohort): Response
    {
        $cohort->load(['course', 'instructors.profile']);

        return Inertia::render('Public/Cohorts/Show', [
            'cohort' => [
                'name' => $cohort->name,
                'slug' => $cohort->slug,
                'start_date' => $cohort->start_date->toFormattedDateString(),
                'end_date' => $cohort->end_date->toFormattedDateString(),
                'status' => $cohort->statusLabel(),
                'accepting_enrollment' => $cohort->isAcceptingEnrollment(),
                'capacity' => $cohort->capacity,
                'live_platform' => $cohort->live_platform,
                'learning_model' => str_replace('_', ' & ', $cohort->learning_model),
            ],
            'course' => [
                'title' => $cohort->course->title,
                'slug' => $cohort->course->slug,
                'price' => $cohort->course->formattedPrice(),
            ],
            'instructors' => $cohort->instructors->map(fn ($i) => [
                'name' => $i->name,
                'headline' => $i->profile?->headline,
            ]),
        ]);
    }
}
