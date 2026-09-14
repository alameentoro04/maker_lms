<?php

namespace Database\Factories;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory()->assignmentType(),
            'instructions' => fake()->paragraph(),
            'due_at' => null,
            'max_file_size_kb' => 10240,
            'max_submissions' => 1,
            'allow_resubmission' => false,
            'passing_score' => null,
        ];
    }
}
