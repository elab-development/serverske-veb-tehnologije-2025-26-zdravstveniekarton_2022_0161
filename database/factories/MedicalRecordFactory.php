<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalRecord>
 */
class MedicalRecordFactory extends Factory
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
            'blood_type' => fake()->optional()->randomElement([
                'A+',
                'A-',
                'B+',
                'B-',
                'AB+',
                'AB-',
                'O+',
                'O-',
            ]),
            'allergies' => fake()->optional(0.45)->sentence(),
            'chronic_conditions' => fake()->optional(0.35)->sentence(),
            'current_medications' => fake()->optional(0.45)->sentence(),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->phoneNumber(),
            'insurance_number' => fake()->optional()->numerify('INS########'),
        ];
    }
}
