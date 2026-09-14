<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('enrollments.view') || $user->hasPermission('enrollments.manage');
    }

    public function view(User $user, Enrollment $enrollment): bool
    {
        return $this->viewAny($user) || $user->id === $enrollment->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('enrollments.manage');
    }

    public function update(User $user, Enrollment $enrollment): bool
    {
        return $user->hasPermission('enrollments.manage');
    }
}
