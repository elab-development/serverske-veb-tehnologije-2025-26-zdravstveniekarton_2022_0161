<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            // Spoljni ključ koji povezuje karton sa korisnikom (pacijentom)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('blood_type'); // Krvna grupa
            $table->text('allergies')->nullable(); // Alergije (može biti prazno)
            $table->text('chronic_diseases')->nullable(); // Hronične bolesti
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};