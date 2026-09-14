<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class InstructorController extends Controller
{
    public function show(User $user): Response
    {
        abort_unless($user->hasRole(Role::INSTRUCTOR), 404);

        $user->load(['profile', 'instructorCourses' => fn ($q) => $q->published()]);

        return Inertia::render('Public/Instructors/Show', [
            'instructor' => [
                'name' => $user->name,
                'headline' => $user->profile?->headline,
                'bio' => $user->profile?->bio,
                'social_links' => $user->profile?->social_links ?? [],
            ],
            'courses' => $user->instructorCourses->map(fn ($c) => [
                'title' => $c->title,
                'slug' => $c->slug,
                'summary' => $c->summary,
            ]),
        ]);
    }
}
