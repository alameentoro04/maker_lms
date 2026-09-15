<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => 'ORD-'.now()->year.'-'.strtoupper(Str::random(8)),
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'cohort_id' => null,
            'amount' => 500000,
            'currency' => 'NGN',
            'status' => 'pending',
        ];
    }
}
