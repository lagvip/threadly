<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'name' => 'Administrator',
        'slug' => 'admin',
        'permissions' => json_encode([
            'admin.access' => true,
            'user.view' => true,
            'user.create' => true,
            'user.edit' => true,
            'user.delete' => true,
            'product.manage' => true,
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ];
}

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}