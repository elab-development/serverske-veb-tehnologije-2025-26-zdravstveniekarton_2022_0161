<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@ehealth.test',
        ]);

        User::factory()->doctor()->create([
            'name' => 'Dr Ana Markovic',
            'email' => 'ana.markovic@ehealth.test',
        ]);

        User::factory()->doctor()->create([
            'name' => 'Dr Nikola Petrovic',
            'email' => 'nikola.petrovic@ehealth.test',
        ]);

        User::factory()->nurse()->create([
            'name' => 'Milica Jovanovic',
            'email' => 'milica.jovanovic@ehealth.test',
        ]);

        User::factory()->nurse()->create([
            'name' => 'Jelena Ilic',
            'email' => 'jelena.ilic@ehealth.test',
        ]);

        User::factory()->nurse()->create([
            'name' => 'Marko Stojanovic',
            'email' => 'marko.stojanovic@ehealth.test',
        ]);

        User::factory(20)->patient()->create();
    }
}
