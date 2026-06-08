<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => User::factory()->patient(),
            'doctor_id' => User::factory()->doctor(),
            'nurse_id' => fake()->boolean(75) ? User::factory()->nurse() : null,
            'scheduled_at' => fake()->dateTimeBetween('now', '+30 days'),
            'reason' => fake()->sentence(),
            'status' => fake()->randomElement([
                Appointment::STATUS_REQUESTED,
                Appointment::STATUS_SCHEDULED,
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_COMPLETED,
                Appointment::STATUS_CANCELLED,
            ]),
            'patient_note' => fake()->optional()->sentence(),
            'nurse_note' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the appointment is requested.
     */
    public function requested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_REQUESTED,
        ]);
    }

    /**
     * Indicate that the appointment is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_SCHEDULED,
        ]);
    }

    /**
     * Indicate that the patient has checked in.
     */
    public function checkedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_CHECKED_IN,
        ]);
    }

    /**
     * Indicate that the appointment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_COMPLETED,
        ]);
    }

    /**
     * Indicate that the appointment is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_CANCELLED,
        ]);
    }
}
