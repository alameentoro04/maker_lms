<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'role_id' => Role::query()->where('slug', Role::STUDENT)->value('id')
                ?? Role::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'status' => 'active',
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function role(string $slug): static
    {
        return $this->state(fn () => [
            'role_id' => Role::query()->where('slug', $slug)->value('id'),
        ]);
    }
}
