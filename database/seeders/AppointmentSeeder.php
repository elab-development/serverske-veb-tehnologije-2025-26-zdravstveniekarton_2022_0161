<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ana = User::where('email', 'ana.markovic@ehealth.test')->firstOrFail();
        $nikola = User::where('email', 'nikola.petrovic@ehealth.test')->firstOrFail();
        $milica = User::where('email', 'milica.jovanovic@ehealth.test')->firstOrFail();
        $jelena = User::where('email', 'jelena.ilic@ehealth.test')->firstOrFail();
        $marko = User::where('email', 'marko.stojanovic@ehealth.test')->firstOrFail();

        $milan = User::where('email', 'milan.jovanovic@patients.ehealth.test')->firstOrFail();
        $ivana = User::where('email', 'ivana.nikolic@patients.ehealth.test')->firstOrFail();
        $petar = User::where('email', 'petar.ilic@patients.ehealth.test')->firstOrFail();
        $sara = User::where('email', 'sara.stankovic@patients.ehealth.test')->firstOrFail();
        $dusan = User::where('email', 'dusan.popovic@patients.ehealth.test')->firstOrFail();

        Appointment::create([
            'patient_id' => $milan->id,
            'doctor_id' => $ana->id,
            'nurse_id' => $milica->id,
            'scheduled_at' => now()->subDays(10)->setTime(9, 0),
            'reason' => 'Kontrolni kardioloski pregled',
            'status' => Appointment::STATUS_COMPLETED,
            'patient_note' => 'Patient reports occasional dizziness in the morning.',
            'nurse_note' => 'Blood pressure elevated during intake.',
        ]);

        Appointment::create([
            'patient_id' => $ivana->id,
            'doctor_id' => $nikola->id,
            'nurse_id' => $jelena->id,
            'scheduled_at' => now()->subDays(4)->setTime(11, 30),
            'reason' => 'Pogorsanje simptoma astme',
            'status' => Appointment::STATUS_COMPLETED,
            'patient_note' => 'Shortness of breath after exercise.',
            'nurse_note' => 'Oxygen saturation stable at intake.',
        ]);

        Appointment::create([
            'patient_id' => $petar->id,
            'doctor_id' => $ana->id,
            'nurse_id' => $marko->id,
            'scheduled_at' => now()->addDays(2)->setTime(10, 15),
            'reason' => 'Kontrola secera u krvi',
            'status' => Appointment::STATUS_SCHEDULED,
            'patient_note' => 'Patient will bring recent lab results.',
            'nurse_note' => null,
        ]);

        Appointment::create([
            'patient_id' => $sara->id,
            'doctor_id' => $nikola->id,
            'nurse_id' => $milica->id,
            'scheduled_at' => now()->addDays(1)->setTime(14, 0),
            'reason' => 'Preventivni sistematski pregled',
            'status' => Appointment::STATUS_CHECKED_IN,
            'patient_note' => 'No current symptoms.',
            'nurse_note' => 'Patient checked in and waiting for doctor.',
        ]);

        Appointment::create([
            'patient_id' => $dusan->id,
            'doctor_id' => $ana->id,
            'nurse_id' => null,
            'scheduled_at' => now()->addDays(7)->setTime(8, 45),
            'reason' => 'Ceste migrene',
            'status' => Appointment::STATUS_REQUESTED,
            'patient_note' => 'Headaches are more frequent during workdays.',
            'nurse_note' => null,
        ]);

        $patients = User::where('role', User::ROLE_PATIENT)->get();
        $doctors = User::where('role', User::ROLE_DOCTOR)->get();
        $nurses = User::where('role', User::ROLE_NURSE)->get();

        for ($i = 0; $i < 4; $i++) {
            Appointment::factory()->create([
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'nurse_id' => fake()->boolean(75) ? $nurses->random()->id : null,
            ]);
        }
    }
}
