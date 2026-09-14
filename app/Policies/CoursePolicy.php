<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('courses.view') || $user->hasPermission('courses.manage');
    }

    public function view(User $user, Course $course): bool
    {
        return $this->viewAny($user) || $course->instructors()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('courses.manage');
    }

    /** Instructors may update the content of courses they're assigned to; only courses.manage can change status/pricing/category (enforced in the controller/request, not here). */
    public function update(User $user, Course $course): bool
    {
        return $user->hasPermission('courses.manage')
            || $course->instructors()->where('users.id', $user->id)->exists();
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->hasPermission('courses.manage');
    }
}
