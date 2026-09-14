<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InstructorSeeder extends Seeder
{
    /**
     * Dev-only placeholder instructor so Course/Cohort seeders have someone
     * real to assign. Replace with an actual instructor account (and drop
     * this seeder from DatabaseSeeder) before launch.
     */
    public function run(): void
    {
        $role = Role::query()->where('slug', Role::INSTRUCTOR)->firstOrFail();

        $instructor = User::query()->updateOrCreate(
            ['email' => 'instructor@makers.al-ismail.com.ng'],
            [
                'role_id' => $role->id,
                'name' => 'Lead Instructor (placeholder)',
                'password' => Hash::make('ChangeMe!12345'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );

        UserProfile::query()->updateOrCreate(
            ['user_id' => $instructor->id],
            [
                'headline' => 'Senior Graphic Designer',
                'bio' => 'Placeholder bio — replace with the real instructor\'s background before launch.',
                'country' => 'Nigeria',
            ]
        );
    }
}
