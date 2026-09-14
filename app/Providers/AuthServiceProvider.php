<?php

namespace App\Providers;

use App\Models\Cohort;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Role;
use App\Policies\CohortPolicy;
use App\Policies\CoursePolicy;
use App\Policies\EnrollmentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Course::class => CoursePolicy::class,
        Cohort::class => CohortPolicy::class,
        Enrollment::class => EnrollmentPolicy::class,
    ];

    public function boot(): void
    {
        // Super Admin bypasses every Policy and Gate check platform-wide.
        // This is the ONLY blanket bypass in the system — every other role
        // is checked permission-by-permission via User::hasPermission().
        Gate::before(function ($user, string $ability) {
            return $user->hasRole(Role::SUPER_ADMIN) ? true : null;
        });
    }
}
