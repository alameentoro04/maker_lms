<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Phase-1 permission set. Later phases (payments, certificates, community, ...)
     * append their own groups here rather than replacing this list.
     */
    public function run(): void
    {
        $permissions = [
            'users' => ['users.view', 'users.manage'],
            'courses' => ['courses.view', 'courses.manage'],
            'cohorts' => ['cohorts.view', 'cohorts.manage'],
            'enrollments' => ['enrollments.view', 'enrollments.manage'],
            'payments' => ['payments.view', 'payments.manage'],
            'certificates' => ['certificates.view', 'certificates.manage'],
            'settings' => ['settings.manage'],
            'reports' => ['reports.view'],
        ];

        foreach ($permissions as $group => $slugs) {
            foreach ($slugs as $slug) {
                Permission::query()->updateOrCreate(
                    ['slug' => $slug],
                    ['group' => $group, 'label' => ucwords(str_replace(['.', '_'], [' ', ' '], $slug))]
                );
            }
        }

        $this->assignDefaults();
    }

    private function assignDefaults(): void
    {
        $all = Permission::all();

        $superAdmin = Role::query()->where('slug', Role::SUPER_ADMIN)->first();
        $superAdmin?->permissions()->sync($all->pluck('id')); // belt-and-braces; Gate::before() already bypasses this

        $admin = Role::query()->where('slug', Role::ADMIN)->first();
        $admin?->permissions()->sync(
            $all->whereNotIn('slug', ['settings.manage'])->pluck('id')
        );

        $instructor = Role::query()->where('slug', Role::INSTRUCTOR)->first();
        $instructor?->permissions()->sync(
            $all->whereIn('slug', ['courses.view', 'cohorts.view', 'enrollments.view'])->pluck('id')
        );

        $staff = Role::query()->where('slug', Role::STAFF)->first();
        $staff?->permissions()->sync(
            $all->whereIn('slug', ['payments.view', 'payments.manage'])->pluck('id')
        );

        // Student intentionally gets no admin-side permissions.
    }
}
