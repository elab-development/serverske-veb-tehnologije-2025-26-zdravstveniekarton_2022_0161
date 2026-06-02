<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'nurse_id' => $this->nurse_id,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'reason' => $this->reason,
            'status' => $this->status,
            'patient_note' => $this->patient_note,
            'nurse_note' => $this->nurse_note,
            'patient' => UserResource::make($this->whenLoaded('patient')),
            'doctor' => UserResource::make($this->whenLoaded('doctor')),
            'nurse' => UserResource::make($this->whenLoaded('nurse')),
            'medical_visit' => MedicalVisitResource::make($this->whenLoaded('medicalVisit')),
        ];
    }
}
