<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CohortFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $start = now()->addWeeks(2);

        return [
            'course_id' => Course::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'start_date' => $start,
            'end_date' => (clone $start)->addWeeks(4),
            'enrollment_opens_at' => now()->subDay(),
            'enrollment_closes_at' => $start,
            'capacity' => 2, // deliberately small in tests to exercise the capacity rule
            'live_platform' => 'google_meet',
            'learning_model' => 'recorded_and_live',
            'status' => 'enrollment_open',
        ];
    }
}
