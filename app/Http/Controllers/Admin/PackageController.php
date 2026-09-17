<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackageRequest;
use App\Models\Cohort;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PackageController extends Controller
{
    public function index(): Response
    {
        abort_unless(request()->user()->hasPermission('courses.manage'), 403);

        return Inertia::render('Admin/Packages/Index', [
            'packages' => Package::query()->withCount('cohorts')->latest()->get()->map(fn (Package $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'price' => $p->formattedPrice(),
                'is_published' => $p->is_published,
                'cohorts_count' => $p->cohorts_count,
            ]),
        ]);
    }

    public function create(): Response
    {
        return $this->formResponse();
    }

    public function store(PackageRequest $request): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('courses.manage'), 403);

        $validated = $request->validated();
        $package = Package::query()->create(collect($validated)->except('cohort_ids')->toArray());
        $package->cohorts()->sync($validated['cohort_ids']);

        return to_route('admin.packages.edit', $package)->with('status', 'Package created.');
    }

    public function edit(Package $package): Response
    {
        return $this->formResponse($package);
    }

    public function update(PackageRequest $request, Package $package): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('courses.manage'), 403);

        $validated = $request->validated();
        $package->update(collect($validated)->except('cohort_ids')->toArray());
        $package->cohorts()->sync($validated['cohort_ids']);

        return back()->with('status', 'Package updated.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        abort_unless(request()->user()->hasPermission('courses.manage'), 403);

        $package->delete();

        return to_route('admin.packages.index')->with('status', 'Package deleted.');
    }

    private function formResponse(?Package $package = null): Response
    {
        abort_unless(request()->user()->hasPermission('courses.manage'), 403);

        $package?->load('cohorts');

        return Inertia::render('Admin/Packages/Form', [
            'package' => $package ? [
                'id' => $package->id,
                'title' => $package->title,
                'slug' => $package->slug,
                'description' => $package->description,
                'price' => $package->price,
                'currency' => $package->currency,
                'is_published' => $package->is_published,
                'cohort_ids' => $package->cohorts->pluck('id'),
            ] : null,
            'cohorts' => Cohort::query()->with('course')->get()->map(fn (Cohort $c) => [
                'id' => $c->id,
                'label' => "{$c->course->title} — {$c->name}",
            ]),
        ]);
    }
}
