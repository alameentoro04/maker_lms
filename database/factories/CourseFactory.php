<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => ucfirst($title),
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'objectives' => [fake()->sentence(), fake()->sentence()],
            'requirements' => [fake()->sentence()],
            'level' => 'beginner',
            'duration_weeks' => 4,
            'status' => 'published',
            'price' => 0,
            'currency' => 'NGN',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }
}
