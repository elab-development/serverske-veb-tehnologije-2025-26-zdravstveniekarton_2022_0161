<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
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
            'blood_type' => $this->blood_type,
            'allergies' => $this->allergies,
            'chronic_conditions' => $this->chronic_conditions,
            'current_medications' => $this->current_medications,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'insurance_number' => $this->insurance_number,
            'patient' => UserResource::make($this->whenLoaded('patient')),
            'medical_visits' => MedicalVisitResource::collection($this->whenLoaded('medicalVisits')),
        ];
    }
}
