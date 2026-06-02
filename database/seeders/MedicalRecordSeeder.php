<?php

namespace Database\Seeders;

use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicalRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
            [
                'email' => 'milan.jovanovic@patients.ehealth.test',
                'blood_type' => 'A+',
                'allergies' => 'Penicillin allergy.',
                'chronic_conditions' => 'Hypertension diagnosed in 2021.',
                'current_medications' => 'Amlodipine 5mg once daily.',
                'emergency_contact_name' => 'Marija Jovanovic',
                'emergency_contact_phone' => '+381 60 111 2222',
                'insurance_number' => 'INS10000001',
            ],
            [
                'email' => 'ivana.nikolic@patients.ehealth.test',
                'blood_type' => 'O-',
                'allergies' => 'No known allergies.',
                'chronic_conditions' => 'Asthma.',
                'current_medications' => 'Salbutamol inhaler as needed.',
                'emergency_contact_name' => 'Marko Nikolic',
                'emergency_contact_phone' => '+381 60 222 3333',
                'insurance_number' => 'INS10000002',
            ],
            [
                'email' => 'petar.ilic@patients.ehealth.test',
                'blood_type' => 'B+',
                'allergies' => null,
                'chronic_conditions' => 'Type 2 diabetes.',
                'current_medications' => 'Metformin 500mg twice daily.',
                'emergency_contact_name' => 'Jelena Ilic',
                'emergency_contact_phone' => '+381 60 333 4444',
                'insurance_number' => 'INS10000003',
            ],
            [
                'email' => 'sara.stankovic@patients.ehealth.test',
                'blood_type' => 'AB+',
                'allergies' => 'Latex sensitivity.',
                'chronic_conditions' => null,
                'current_medications' => null,
                'emergency_contact_name' => 'Nikola Stankovic',
                'emergency_contact_phone' => '+381 60 444 5555',
                'insurance_number' => 'INS10000004',
            ],
            [
                'email' => 'dusan.popovic@patients.ehealth.test',
                'blood_type' => 'O+',
                'allergies' => 'Peanut allergy.',
                'chronic_conditions' => 'Migraine.',
                'current_medications' => 'Ibuprofen as needed.',
                'emergency_contact_name' => 'Ana Popovic',
                'emergency_contact_phone' => '+381 60 555 6666',
                'insurance_number' => 'INS10000005',
            ],
        ];

        foreach ($records as $record) {
            $patient = User::where('email', $record['email'])->firstOrFail();

            unset($record['email']);

            MedicalRecord::create([
                'patient_id' => $patient->id,
                ...$record,
            ]);
        }

        User::where('role', User::ROLE_PATIENT)
            ->whereDoesntHave('medicalRecord')
            ->take(3)
            ->get()
            ->each(function (User $patient): void {
                MedicalRecord::factory()->create([
                    'patient_id' => $patient->id,
                ]);
            });
    }
}
