<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'cohort_id' => null,
            'status' => 'active',
            'enrolled_at' => now(),
            'access_starts_at' => now(),
        ];
    }
}
