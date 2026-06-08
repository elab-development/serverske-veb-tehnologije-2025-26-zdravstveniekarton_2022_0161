<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE medical_visits
            ADD CONSTRAINT medical_visits_temperature_check
            CHECK (temperature IS NULL OR temperature BETWEEN 34.0 AND 43.0)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            'ALTER TABLE medical_visits
            DROP CHECK medical_visits_temperature_check'
        );
    }
};
