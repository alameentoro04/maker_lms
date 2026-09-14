<?php

namespace App\Providers;

use App\Models\Role;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // App\Models\Course::class => App\Policies\CoursePolicy::class,  (added in Phase 3)
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
