<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            // Spoljni kljucevi (Tip 2) koji povezuju pregled sa pacijentom i lekarom
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->string('appointment_date'); // Privremeno kao string da bismo kasnije menjali
            $table->dateTime('appointment_date'); // Datum i vreme pregleda
            $table->text('symptoms')->nullable(); // Simptomi koje pacijent navodi
            $table->text('diagnosis')->nullable(); // Dijagnoza koju doktor upisuje
            $table->string('status')->default('zakazan'); // Status: zakazan, zavrsen, otkazan
            $table->timestamps();
            
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};