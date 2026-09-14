<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CertificateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'certificate_id' => 'MKR-GD-'.now()->year.'-'.fake()->unique()->numerify('######'),
            'holder_name' => fake()->name(),
            'course_title' => 'Graphic Design',
            'cohort_label' => 'Graphic Design — Cohort 1',
            'issued_at' => now(),
            'status' => 'active',
        ];
    }
}
