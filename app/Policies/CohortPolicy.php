<?php

namespace App\Policies;

use App\Models\Cohort;
use App\Models\User;

class CohortPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('cohorts.view') || $user->hasPermission('cohorts.manage');
    }

    public function view(User $user, Cohort $cohort): bool
    {
        return $this->viewAny($user) || $cohort->instructors()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('cohorts.manage');
    }

    public function update(User $user, Cohort $cohort): bool
    {
        return $user->hasPermission('cohorts.manage');
    }

    public function delete(User $user, Cohort $cohort): bool
    {
        return $user->hasPermission('cohorts.manage');
    }
}
