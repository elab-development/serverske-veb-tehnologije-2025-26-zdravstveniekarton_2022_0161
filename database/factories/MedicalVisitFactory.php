<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\MedicalVisit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalVisit>
 */
class MedicalVisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'appointment_id' => null,
            'doctor_id' => User::factory()->doctor(),
            'nurse_id' => fake()->boolean(75) ? User::factory()->nurse() : null,
            'symptoms' => fake()->sentence(),
            'temperature' => fake()->optional(0.8)->randomFloat(1, 35.5, 39.5),
            'blood_pressure' => fake()->optional(0.8)->randomElement([
                '110/70',
                '120/80',
                '130/85',
                '140/90',
            ]),
            'heart_rate' => fake()->optional(0.8)->numberBetween(55, 115),
            'diagnosis' => fake()->optional(0.65)->sentence(),
            'therapy' => fake()->optional(0.65)->sentence(),
            'prescription' => fake()->optional(0.45)->sentence(),
            'doctor_note' => fake()->optional()->sentence(),
            'nurse_note' => fake()->optional()->sentence(),
            'follow_up_at' => fake()->optional(0.35)->dateTimeBetween('+1 week', '+3 months'),
            'status' => fake()->randomElement([
                MedicalVisit::STATUS_DRAFT,
                MedicalVisit::STATUS_FINALIZED,
            ]),
        ];
    }

    /**
     * Indicate that the medical visit is still a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MedicalVisit::STATUS_DRAFT,
        ]);
    }

    /**
     * Indicate that the medical visit is finalized.
     */
    public function finalized(): static
    {
        return $this->state(fn (array $attributes) => [
            'diagnosis' => $attributes['diagnosis'] ?? fake()->sentence(),
            'therapy' => $attributes['therapy'] ?? fake()->sentence(),
            'status' => MedicalVisit::STATUS_FINALIZED,
        ]);
    }
}
