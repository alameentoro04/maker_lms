<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            PlatformSettingsSeeder::class,
            AdminUserSeeder::class,
            // Phase 2 — public-site demo/dev content. InstructorSeeder, TestimonialSeeder,
            // and ProjectShowcaseSeeder create clearly-labelled placeholder data;
            // see their docblocks before running this against production.
            CategorySeeder::class,
            InstructorSeeder::class,
            CourseSeeder::class,
            CohortSeeder::class,
            TestimonialSeeder::class,
            ProjectShowcaseSeeder::class,
            CertificateDemoSeeder::class,
        ]);
    }
}
