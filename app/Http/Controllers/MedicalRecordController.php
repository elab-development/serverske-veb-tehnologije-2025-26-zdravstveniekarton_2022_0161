<?php

namespace App\Http\Controllers;

use App\Http\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
        ]);

        $query = MedicalRecord::query()
            ->with('patient')
            ->orderBy('created_at', 'desc');

        $this->applyVisibilityScope($query, $user);

        if (isset($validated['user_id'])) {
            $query->where('patient_id', $validated['user_id']);
        }

        $medicalRecords = $query->get();

        return response()->json([
            'count' => $medicalRecords->count(),
            'filters' => $request->only('user_id'),
            'medical_records' => MedicalRecordResource::collection($medicalRecords),
        ]);
    }

    public function show(Request $request, MedicalRecord $medicalRecord): JsonResponse
    {
        if (! $this->canView($request->user(), $medicalRecord)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'medical_record' => new MedicalRecordResource($medicalRecord->load('patient')),
        ]);
    }

    public function update(Request $request, MedicalRecord $medicalRecord): JsonResponse
    {
        $user = $request->user();

        if (! $this->canUpdate($user, $medicalRecord)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $fields = $this->updatableFields($user);

        $validated = $request->validate($this->rules($fields));
        $data = array_intersect_key($validated, array_flip($fields));

        if ($data === []) {
            return response()->json([
                'message' => 'Nothing to update.',
                'medical_record' => new MedicalRecordResource($medicalRecord->load('patient')),
            ]);
        }

        $medicalRecord->update($data);

        return response()->json([
            'message' => 'Medical record updated successfully.',
            'medical_record' => new MedicalRecordResource($medicalRecord->refresh()->load('patient')),
        ]);
    }

    private function applyVisibilityScope(Builder $query, User $user): void
    {
        if ($user->role === User::ROLE_ADMIN) {
            return;
        }

        if ($user->role === User::ROLE_PATIENT) {
            $query->where('patient_id', $user->id);

            return;
        }

        if ($user->role === User::ROLE_DOCTOR) {
            $query->where(function (Builder $query) use ($user): void {
                $query->whereHas('patient.patientAppointments', function (Builder $query) use ($user): void {
                    $query->where('doctor_id', $user->id);
                })->orWhereHas('medicalVisits', function (Builder $query) use ($user): void {
                    $query->where('doctor_id', $user->id);
                });
            });

            return;
        }

        if ($user->role === User::ROLE_NURSE) {
            $query->where(function (Builder $query) use ($user): void {
                $query->whereHas('patient.patientAppointments', function (Builder $query) use ($user): void {
                    $query->where('nurse_id', $user->id);
                })->orWhereHas('medicalVisits', function (Builder $query) use ($user): void {
                    $query->where('nurse_id', $user->id);
                });
            });

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function canView(User $user, MedicalRecord $medicalRecord): bool
    {
        if ($user->role === User::ROLE_ADMIN) {
            return true;
        }

        if ($user->role === User::ROLE_PATIENT) {
            return $medicalRecord->patient_id === $user->id;
        }

        if ($user->role === User::ROLE_DOCTOR) {
            return $medicalRecord->patient()
                ->whereHas('patientAppointments', function (Builder $query) use ($user): void {
                    $query->where('doctor_id', $user->id);
                })
                ->exists()
                || $medicalRecord->medicalVisits()
                    ->where('doctor_id', $user->id)
                    ->exists();
        }

        if ($user->role === User::ROLE_NURSE) {
            return $medicalRecord->patient()
                ->whereHas('patientAppointments', function (Builder $query) use ($user): void {
                    $query->where('nurse_id', $user->id);
                })
                ->exists()
                || $medicalRecord->medicalVisits()
                    ->where('nurse_id', $user->id)
                    ->exists();
        }

        return false;
    }

    private function canUpdate(User $user, MedicalRecord $medicalRecord): bool
    {
        if (! in_array($user->role, [User::ROLE_PATIENT, User::ROLE_NURSE, User::ROLE_DOCTOR], true)) {
            return false;
        }

        return $this->canView($user, $medicalRecord);
    }

    /**
     * @return list<string>
     */
    private function updatableFields(User $user): array
    {
        return match ($user->role) {
            User::ROLE_DOCTOR => [
                'blood_type',
                'allergies',
                'chronic_conditions',
                'current_medications',
                'emergency_contact_name',
                'emergency_contact_phone',
                'insurance_number',
            ],
            User::ROLE_NURSE => [
                'blood_type',
                'allergies',
                'current_medications',
                'emergency_contact_name',
                'emergency_contact_phone',
                'insurance_number',
            ],
            User::ROLE_PATIENT => [
                'emergency_contact_name',
                'emergency_contact_phone',
                'insurance_number',
            ],
            default => [],
        };
    }

    /**
     * @param list<string> $fields
     *
     * @return array<string, mixed>
     */
    private function rules(array $fields): array
    {
        $rules = [
            'blood_type' => ['sometimes', 'nullable', 'string', 'max:10'],
            'allergies' => ['sometimes', 'nullable', 'string'],
            'chronic_conditions' => ['sometimes', 'nullable', 'string'],
            'current_medications' => ['sometimes', 'nullable', 'string'],
            'emergency_contact_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'insurance_number' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];

        return array_intersect_key($rules, array_flip($fields));
    }
}
