<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'title' => fake()->sentence(3),
            'type' => 'text',
            'content' => fake()->paragraph(),
            'order' => 0,
            'is_preview' => false,
            'is_published' => true,
        ];
    }

    public function preview(): static
    {
        return $this->state(fn () => ['is_preview' => true]);
    }

    public function video(): static
    {
        return $this->state(fn () => ['type' => 'video', 'video_provider' => 'mock', 'video_reference' => 'mock_'.fake()->uuid()]);
    }

    public function assignmentType(): static
    {
        return $this->state(fn () => ['type' => 'assignment', 'content' => null]);
    }
}
