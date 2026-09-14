<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class CohortSeeder extends Seeder
{
    /**
     * Uses the confirmed defaults from the spec: 4 weeks, 30 max students,
     * enrollment disabled after start, Google Meet, recorded + live.
     */
    public function run(): void
    {
        $course = Course::query()->where('slug', 'graphic-design')->first();

        if (! $course) {
            return;
        }

        $start = now()->addWeeks(2)->startOfDay();

        $cohort = Cohort::query()->updateOrCreate(
            ['slug' => 'graphic-design-cohort-1'],
            [
                'course_id' => $course->id,
                'name' => 'Graphic Design — Cohort 1',
                'start_date' => $start,
                'end_date' => (clone $start)->addWeeks(4),
                'enrollment_opens_at' => now(),
                'enrollment_closes_at' => $start, // enrollment disabled after start, per spec default
                'capacity' => 30,
                'live_platform' => 'google_meet',
                'learning_model' => 'recorded_and_live',
                'status' => 'enrollment_open',
            ]
        );

        $instructor = User::query()->where('email', 'instructor@makers.al-ismail.com.ng')->first();

        if ($instructor) {
            $cohort->instructors()->syncWithoutDetaching([$instructor->id]);
        }
    }
}
