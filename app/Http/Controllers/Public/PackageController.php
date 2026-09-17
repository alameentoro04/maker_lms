<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Inertia\Inertia;
use Inertia\Response;

class PackageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Packages/Index', [
            'packages' => Package::query()->published()->withCount('cohorts')->get()->map(fn (Package $p) => [
                'title' => $p->title,
                'slug' => $p->slug,
                'description' => $p->description,
                'price' => $p->formattedPrice(),
                'cohorts_count' => $p->cohorts_count,
            ]),
        ]);
    }

    public function show(Package $package): Response
    {
        abort_unless($package->is_published, 404);

        $package->load('cohorts.course');

        return Inertia::render('Public/Packages/Show', [
            'package' => [
                'title' => $package->title,
                'slug' => $package->slug,
                'description' => $package->description,
                'price' => $package->formattedPrice(),
            ],
            'cohorts' => $package->cohorts->map(fn ($c) => [
                'course_title' => $c->course->title,
                'name' => $c->name,
                'start_date' => $c->start_date->toFormattedDateString(),
            ]),
        ]);
    }
}
