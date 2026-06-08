<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => User::ROLE_PATIENT,
            'remember_token' => Str::random(10),
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

    /**
     * Indicate that the user is a patient.
     */
    public function patient(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_PATIENT,
        ]);
    }

    /**
     * Indicate that the user is a nurse.
     */
    public function nurse(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_NURSE,
        ]);
    }

    /**
     * Indicate that the user is a doctor.
     */
    public function doctor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_DOCTOR,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
        ]);
    }
}
