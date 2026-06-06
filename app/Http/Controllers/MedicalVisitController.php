<?php

namespace App\Http\Controllers;

use App\Http\Resources\MedicalVisitResource;
use App\Models\Appointment;
use App\Models\MedicalVisit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MedicalVisitController extends Controller
{
    private const STATUSES = [
        MedicalVisit::STATUS_DRAFT,
        MedicalVisit::STATUS_FINALIZED,
    ];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === User::ROLE_ADMIN) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'search' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'patient_id' => ['sometimes', 'integer', 'exists:users,id'],
            'doctor_id' => ['sometimes', 'integer', 'exists:users,id'],
            'nurse_id' => ['sometimes', 'integer', 'exists:users,id'],
            'appointment_id' => ['sometimes', 'integer', 'exists:appointments,id'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $query = MedicalVisit::query()
            ->with(['medicalRecord.patient', 'appointment', 'doctor', 'nurse'])
            ->latest();

        $this->applyVisibilityScope($query, $user);
        $this->applyFilters($query, $validated);

        $medicalVisits = $query
            ->paginate((int) ($validated['per_page'] ?? 10))
            ->withQueryString();

        return response()->json([
            'count' => $medicalVisits->count(),
            'total' => $medicalVisits->total(),
            'per_page' => $medicalVisits->perPage(),
            'current_page' => $medicalVisits->currentPage(),
            'last_page' => $medicalVisits->lastPage(),
            'filters' => $request->only([
                'search',
                'status',
                'patient_id',
                'doctor_id',
                'nurse_id',
                'appointment_id',
            ]),
            'medical_visits' => MedicalVisitResource::collection($medicalVisits->getCollection()),
        ]);
    }

    public function show(Request $request, MedicalVisit $medicalVisit): JsonResponse
    {
        if (! $this->canView($request->user(), $medicalVisit)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'medical_visit' => new MedicalVisitResource(
                $medicalVisit->load(['medicalRecord.patient', 'appointment', 'doctor', 'nurse'])
            ),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== User::ROLE_NURSE) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'appointment_id' => ['required', 'integer', 'exists:appointments,id'],
            'symptoms' => ['required', 'string'],
            'temperature' => ['sometimes', 'nullable', 'numeric', 'between:34,43'],
            'blood_pressure' => ['sometimes', 'nullable', 'string', 'max:255'],
            'heart_rate' => ['sometimes', 'nullable', 'integer', 'min:30', 'max:220'],
            'nurse_note' => ['sometimes', 'nullable', 'string'],
        ]);

        $appointment = Appointment::with(['patient.medicalRecord', 'medicalVisit'])
            ->findOrFail($validated['appointment_id']);

        if ($appointment->nurse_id !== null && $appointment->nurse_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($appointment->doctor_id === null) {
            return response()->json([
                'message' => 'Appointment must have an assigned doctor before creating a medical visit.',
            ], 422);
        }

        if ($appointment->medicalVisit !== null) {
            return response()->json([
                'message' => 'Medical visit already exists for this appointment.',
            ], 422);
        }

        if ($appointment->status === Appointment::STATUS_CANCELLED) {
            return response()->json([
                'message' => 'Medical visit cannot be created for a cancelled appointment.',
            ], 422);
        }

        if ($appointment->patient->medicalRecord === null) {
            return response()->json([
                'message' => 'Patient does not have a medical record.',
            ], 422);
        }

        $medicalVisit = DB::transaction(function () use ($appointment, $user, $validated): MedicalVisit {
            if ($appointment->nurse_id === null) {
                $appointment->update([
                    'nurse_id' => $user->id,
                ]);
            }

            return MedicalVisit::create([
                'medical_record_id' => $appointment->patient->medicalRecord->id,
                'appointment_id' => $appointment->id,
                'doctor_id' => $appointment->doctor_id,
                'nurse_id' => $appointment->nurse_id,
                'symptoms' => $validated['symptoms'],
                'temperature' => $validated['temperature'] ?? null,
                'blood_pressure' => $validated['blood_pressure'] ?? null,
                'heart_rate' => $validated['heart_rate'] ?? null,
                'nurse_note' => $validated['nurse_note'] ?? null,
                'status' => MedicalVisit::STATUS_DRAFT,
            ]);
        });

        return response()->json([
            'message' => 'Medical visit created successfully.',
            'medical_visit' => new MedicalVisitResource(
                $medicalVisit->load(['medicalRecord.patient', 'appointment', 'doctor', 'nurse'])
            ),
        ], 201);
    }

    public function update(Request $request, MedicalVisit $medicalVisit): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== User::ROLE_DOCTOR || $medicalVisit->doctor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'symptoms' => ['sometimes', 'string'],
            'diagnosis' => ['sometimes', 'nullable', 'string'],
            'therapy' => ['sometimes', 'nullable', 'string'],
            'prescription' => ['sometimes', 'nullable', 'string'],
            'doctor_note' => ['sometimes', 'nullable', 'string'],
            'follow_up_at' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
        ]);

        if ($validated === []) {
            return response()->json([
                'message' => 'Nothing to update.',
                'medical_visit' => new MedicalVisitResource(
                    $medicalVisit->load(['medicalRecord.patient', 'appointment', 'doctor', 'nurse'])
                ),
            ]);
        }

        $medicalVisit->update($validated);

        return response()->json([
            'message' => 'Medical visit updated successfully.',
            'medical_visit' => new MedicalVisitResource(
                $medicalVisit->refresh()->load(['medicalRecord.patient', 'appointment', 'doctor', 'nurse'])
            ),
        ]);
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function (Builder $query) use ($search): void {
                $query->where('symptoms', 'like', "%{$search}%")
                    ->orWhere('diagnosis', 'like', "%{$search}%")
                    ->orWhere('therapy', 'like', "%{$search}%")
                    ->orWhere('prescription', 'like', "%{$search}%")
                    ->orWhere('doctor_note', 'like', "%{$search}%")
                    ->orWhere('nurse_note', 'like', "%{$search}%")
                    ->orWhereHas('appointment', function (Builder $query) use ($search): void {
                        $query->where('reason', 'like', "%{$search}%");
                    })
                    ->orWhereHas('medicalRecord.patient', function (Builder $query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['patient_id'])) {
            $query->whereHas('medicalRecord', function (Builder $query) use ($filters): void {
                $query->where('patient_id', $filters['patient_id']);
            });
        }

        if (isset($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (isset($filters['nurse_id'])) {
            $query->where('nurse_id', $filters['nurse_id']);
        }

        if (isset($filters['appointment_id'])) {
            $query->where('appointment_id', $filters['appointment_id']);
        }
    }

    private function applyVisibilityScope(Builder $query, User $user): void
    {
        if ($user->role === User::ROLE_PATIENT) {
            $query->whereHas('medicalRecord', function (Builder $query) use ($user): void {
                $query->where('patient_id', $user->id);
            });

            return;
        }

        if ($user->role === User::ROLE_DOCTOR) {
            $query->where('doctor_id', $user->id);

            return;
        }

        if ($user->role === User::ROLE_NURSE) {
            $query->where('nurse_id', $user->id);

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function canView(User $user, MedicalVisit $medicalVisit): bool
    {
        return match ($user->role) {
            User::ROLE_PATIENT => $medicalVisit->medicalRecord()
                ->where('patient_id', $user->id)
                ->exists(),
            User::ROLE_DOCTOR => $medicalVisit->doctor_id === $user->id,
            User::ROLE_NURSE => $medicalVisit->nurse_id === $user->id,
            default => false,
        };
    }
}
