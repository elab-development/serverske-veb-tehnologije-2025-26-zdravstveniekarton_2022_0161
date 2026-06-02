<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalVisitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medical_record_id' => $this->medical_record_id,
            'appointment_id' => $this->appointment_id,
            'doctor_id' => $this->doctor_id,
            'nurse_id' => $this->nurse_id,
            'symptoms' => $this->symptoms,
            'temperature' => $this->temperature,
            'blood_pressure' => $this->blood_pressure,
            'heart_rate' => $this->heart_rate,
            'diagnosis' => $this->diagnosis,
            'therapy' => $this->therapy,
            'prescription' => $this->prescription,
            'doctor_note' => $this->doctor_note,
            'nurse_note' => $this->nurse_note,
            'follow_up_at' => $this->follow_up_at?->toISOString(),
            'status' => $this->status,
            'medical_record' => MedicalRecordResource::make($this->whenLoaded('medicalRecord')),
            'appointment' => AppointmentResource::make($this->whenLoaded('appointment')),
            'doctor' => UserResource::make($this->whenLoaded('doctor')),
            'nurse' => UserResource::make($this->whenLoaded('nurse')),
        ];
    }
}
