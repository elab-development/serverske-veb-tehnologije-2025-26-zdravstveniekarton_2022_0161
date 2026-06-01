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
        Schema::create('medical_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('appointment_id')
                ->nullable()
                ->unique()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('doctor_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('nurse_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('symptoms');
            $table->decimal('temperature', 4, 1)->nullable();
            $table->string('blood_pressure')->nullable();
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('therapy')->nullable();
            $table->text('prescription')->nullable();
            $table->text('doctor_note')->nullable();
            $table->text('nurse_note')->nullable();
            $table->dateTime('follow_up_at')->nullable();
            $table->enum('status', ['draft', 'finalized'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_visits');
    }
};
