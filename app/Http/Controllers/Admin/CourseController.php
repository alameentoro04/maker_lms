<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseRequest;
use App\Models\Category;
use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Course::class);

        return Inertia::render('Admin/Courses/Index', [
            'courses' => Course::query()->with('category')->withCount('cohorts')->latest()->get()->map(fn (Course $c) => [
                'id' => $c->id,
                'title' => $c->title,
                'slug' => $c->slug,
                'status' => $c->status,
                'category' => $c->category?->name,
                'cohorts_count' => $c->cohorts_count,
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Course::class);

        return Inertia::render('Admin/Courses/Form', [
            'course' => null,
            'categories' => Category::query()->orderBy('order')->get(['id', 'name']),
            'instructors' => User::query()->where('role_id', Role::query()->where('slug', Role::INSTRUCTOR)->value('id'))->get(['id', 'name']),
        ]);
    }

    public function store(CourseRequest $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $validated = $request->validated();

        $course = DB::transaction(function () use ($validated) {
            $course = Course::query()->create(collect($validated)->except(['objectives', 'requirements', 'instructor_ids'])->toArray() + [
                'objectives' => array_values(array_filter($validated['objectives'] ?? [])),
                'requirements' => array_values(array_filter($validated['requirements'] ?? [])),
            ]);

            $course->instructors()->sync($validated['instructor_ids'] ?? []);

            return $course;
        });

        return to_route('admin.courses.edit', $course)->with('status', 'Course created.');
    }

    public function edit(Course $course): Response
    {
        $this->authorize('update', $course);

        $course->load(['instructors', 'modules.lessons']);

        return Inertia::render('Admin/Courses/Form', [
            'course' => [
                'id' => $course->id,
                'category_id' => $course->category_id,
                'title' => $course->title,
                'slug' => $course->slug,
                'summary' => $course->summary,
                'description' => $course->description,
                'objectives' => $course->objectives ?? [],
                'requirements' => $course->requirements ?? [],
                'level' => $course->level,
                'duration_weeks' => $course->duration_weeks,
                'status' => $course->status,
                'price' => $course->price,
                'currency' => $course->currency,
                'instructor_ids' => $course->instructors->pluck('id'),
            ],
            'categories' => Category::query()->orderBy('order')->get(['id', 'name']),
            'instructors' => User::query()->where('role_id', Role::query()->where('slug', Role::INSTRUCTOR)->value('id'))->get(['id', 'name']),
            'modules' => $course->modules->map(fn ($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'order' => $m->order,
                'lessons' => $m->lessons->map(fn ($l) => [
                    'id' => $l->id,
                    'title' => $l->title,
                    'type' => $l->type,
                    'order' => $l->order,
                    'is_preview' => $l->is_preview,
                    'is_published' => $l->is_published,
                ]),
            ]),
        ]);
    }

    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $course) {
            $course->update(collect($validated)->except(['objectives', 'requirements', 'instructor_ids'])->toArray() + [
                'objectives' => array_values(array_filter($validated['objectives'] ?? [])),
                'requirements' => array_values(array_filter($validated['requirements'] ?? [])),
            ]);

            $course->instructors()->sync($validated['instructor_ids'] ?? []);
        });

        return back()->with('status', 'Course updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $course->delete();

        return to_route('admin.courses.index')->with('status', 'Course deleted.');
    }
}
