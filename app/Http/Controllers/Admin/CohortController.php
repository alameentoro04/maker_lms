<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CohortRequest;
use App\Models\Cohort;
use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CohortController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Cohort::class);

        return Inertia::render('Admin/Cohorts/Index', [
            'cohorts' => Cohort::query()->with('course')->withCount('enrollments')->latest()->get()->map(fn (Cohort $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'course' => $c->course->title,
                'status' => $c->statusLabel(),
                'start_date' => $c->start_date->toFormattedDateString(),
                'capacity' => $c->capacity,
                'enrolled' => $c->activeEnrollmentCount(),
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Cohort::class);

        return $this->formResponse();
    }

    public function store(CohortRequest $request): RedirectResponse
    {
        $this->authorize('create', Cohort::class);

        $validated = $request->validated();

        $cohort = DB::transaction(function () use ($validated) {
            $cohort = Cohort::query()->create(collect($validated)->except('instructor_ids')->toArray());
            $cohort->instructors()->sync($validated['instructor_ids'] ?? []);

            return $cohort;
        });

        return to_route('admin.cohorts.edit', $cohort)->with('status', 'Cohort created.');
    }

    public function edit(Cohort $cohort): Response
    {
        $this->authorize('update', $cohort);

        return $this->formResponse($cohort);
    }

    public function update(CohortRequest $request, Cohort $cohort): RedirectResponse
    {
        $this->authorize('update', $cohort);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $cohort) {
            $cohort->update(collect($validated)->except('instructor_ids')->toArray());
            $cohort->instructors()->sync($validated['instructor_ids'] ?? []);
        });

        return back()->with('status', 'Cohort updated.');
    }

    public function destroy(Cohort $cohort): RedirectResponse
    {
        $this->authorize('delete', $cohort);

        $cohort->delete();

        return to_route('admin.cohorts.index')->with('status', 'Cohort deleted.');
    }

    private function formResponse(?Cohort $cohort = null): Response
    {
        $cohort?->load('instructors');

        return Inertia::render('Admin/Cohorts/Form', [
            'cohort' => $cohort ? [
                'id' => $cohort->id,
                'course_id' => $cohort->course_id,
                'name' => $cohort->name,
                'slug' => $cohort->slug,
                'start_date' => $cohort->start_date->toDateString(),
                'end_date' => $cohort->end_date->toDateString(),
                'enrollment_opens_at' => $cohort->enrollment_opens_at?->toDateTimeLocalString(),
                'enrollment_closes_at' => $cohort->enrollment_closes_at?->toDateTimeLocalString(),
                'capacity' => $cohort->capacity,
                'live_platform' => $cohort->live_platform,
                'learning_model' => $cohort->learning_model,
                'status' => $cohort->status,
                'instructor_ids' => $cohort->instructors->pluck('id'),
            ] : null,
            'courses' => Course::query()->get(['id', 'title']),
            'statuses' => Cohort::STATUSES,
            'instructors' => User::query()->where('role_id', Role::query()->where('slug', Role::INSTRUCTOR)->value('id'))->get(['id', 'name']),
        ]);
    }
}
