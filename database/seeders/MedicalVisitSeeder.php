<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\MedicalVisit;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicalVisitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $milan = User::where('email', 'milan.jovanovic@patients.ehealth.test')->firstOrFail();
        $ivana = User::where('email', 'ivana.nikolic@patients.ehealth.test')->firstOrFail();
        $sara = User::where('email', 'sara.stankovic@patients.ehealth.test')->firstOrFail();

        $milanAppointment = Appointment::where('patient_id', $milan->id)
            ->where('reason', 'Kontrolni kardioloski pregled')
            ->firstOrFail();

        $ivanaAppointment = Appointment::where('patient_id', $ivana->id)
            ->where('reason', 'Pogorsanje simptoma astme')
            ->firstOrFail();

        $saraAppointment = Appointment::where('patient_id', $sara->id)
            ->where('reason', 'Preventivni sistematski pregled')
            ->firstOrFail();

        MedicalVisit::create([
            'medical_record_id' => $milan->medicalRecord->id,
            'appointment_id' => $milanAppointment->id,
            'doctor_id' => $milanAppointment->doctor_id,
            'nurse_id' => $milanAppointment->nurse_id,
            'symptoms' => 'Morning dizziness and occasional headache.',
            'temperature' => 36.8,
            'blood_pressure' => '145/90',
            'heart_rate' => 88,
            'diagnosis' => 'Hypertension, currently not optimally controlled.',
            'therapy' => 'Continue current medication and reduce salt intake.',
            'prescription' => 'Amlodipine 5mg, continue once daily.',
            'doctor_note' => 'Schedule follow-up with blood pressure diary.',
            'nurse_note' => 'Patient advised to rest before repeated measurement.',
            'follow_up_at' => now()->addWeeks(4)->setTime(9, 0),
            'status' => MedicalVisit::STATUS_FINALIZED,
        ]);

        MedicalVisit::create([
            'medical_record_id' => $ivana->medicalRecord->id,
            'appointment_id' => $ivanaAppointment->id,
            'doctor_id' => $ivanaAppointment->doctor_id,
            'nurse_id' => $ivanaAppointment->nurse_id,
            'symptoms' => 'Shortness of breath and dry cough after exercise.',
            'temperature' => 37.1,
            'blood_pressure' => '118/76',
            'heart_rate' => 96,
            'diagnosis' => 'Mild asthma exacerbation.',
            'therapy' => 'Use rescue inhaler before exercise and monitor symptoms.',
            'prescription' => 'Salbutamol inhaler, two puffs as needed.',
            'doctor_note' => 'Consider spirometry if symptoms continue.',
            'nurse_note' => 'Oxygen saturation stable during intake.',
            'follow_up_at' => now()->addWeeks(2)->setTime(11, 30),
            'status' => MedicalVisit::STATUS_FINALIZED,
        ]);

        MedicalVisit::create([
            'medical_record_id' => $sara->medicalRecord->id,
            'appointment_id' => $saraAppointment->id,
            'doctor_id' => $saraAppointment->doctor_id,
            'nurse_id' => $saraAppointment->nurse_id,
            'symptoms' => 'No acute symptoms. Preventive check-up in progress.',
            'temperature' => 36.6,
            'blood_pressure' => '112/72',
            'heart_rate' => 72,
            'diagnosis' => null,
            'therapy' => null,
            'prescription' => null,
            'doctor_note' => 'Doctor review pending.',
            'nurse_note' => 'Initial vital signs within normal range.',
            'follow_up_at' => null,
            'status' => MedicalVisit::STATUS_DRAFT,
        ]);

        $medicalRecords = User::where('role', User::ROLE_PATIENT)
            ->whereHas('medicalRecord')
            ->with('medicalRecord')
            ->get()
            ->pluck('medicalRecord');

        $doctors = User::where('role', User::ROLE_DOCTOR)->get();
        $nurses = User::where('role', User::ROLE_NURSE)->get();

        for ($i = 0; $i < 4; $i++) {
            MedicalVisit::factory()->create([
                'medical_record_id' => $medicalRecords->random()->id,
                'doctor_id' => $doctors->random()->id,
                'nurse_id' => fake()->boolean(75) ? $nurses->random()->id : null,
            ]);
        }
    }
}
