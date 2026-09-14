<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['slug' => Role::SUPER_ADMIN, 'name' => 'Super Admin', 'description' => 'Full platform access, bypasses all permission checks.', 'is_system' => true],
            ['slug' => Role::ADMIN, 'name' => 'Admin', 'description' => 'Academy operations according to assigned permissions.', 'is_system' => true],
            ['slug' => Role::INSTRUCTOR, 'name' => 'Instructor', 'description' => 'Manages assigned courses, cohorts, and students.', 'is_system' => true],
            ['slug' => Role::STAFF, 'name' => 'Staff', 'description' => 'Configurable support/finance/operations role.', 'is_system' => false],
            ['slug' => Role::STUDENT, 'name' => 'Student', 'description' => 'Enrolls in and takes courses.', 'is_system' => true],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
