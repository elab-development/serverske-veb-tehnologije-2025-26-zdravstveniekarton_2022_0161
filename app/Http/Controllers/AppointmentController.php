<?php

namespace App\Http\Controllers;

use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class AppointmentController extends Controller
{
    private const STATUSES = [
        Appointment::STATUS_REQUESTED,
        Appointment::STATUS_SCHEDULED,
        Appointment::STATUS_CHECKED_IN,
        Appointment::STATUS_COMPLETED,
        Appointment::STATUS_CANCELLED,
    ];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === User::ROLE_ADMIN) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Appointment::query()
            ->with(['patient', 'doctor', 'nurse'])
            ->orderByRaw('scheduled_at IS NULL')
            ->orderBy('scheduled_at')
            ->latest();

        if ($user->role === User::ROLE_PATIENT) {
            $query->where('patient_id', $user->id);
        } elseif ($user->role === User::ROLE_DOCTOR) {
            $query->where('doctor_id', $user->id);
        } elseif ($user->role !== User::ROLE_NURSE) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $appointments = $query->get();

        return response()->json([
            'count' => $appointments->count(),
            'appointments' => AppointmentResource::collection($appointments),
        ]);
    }

    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        if (! $this->canView($request->user(), $appointment)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'appointment' => new AppointmentResource($appointment->load(['patient', 'doctor', 'nurse'])),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === User::ROLE_PATIENT) {
            return $this->storePatientAppointment($request, $user);
        }

        if ($user->role === User::ROLE_NURSE) {
            return $this->storeNurseAppointment($request, $user);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        $user = $request->user();

        if ($user->role === User::ROLE_PATIENT) {
            return $this->updatePatientAppointment($request, $appointment, $user);
        }

        if ($user->role === User::ROLE_NURSE) {
            return $this->updateNurseAppointment($request, $appointment);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    private function storePatientAppointment(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string'],
            'patient_note' => ['sometimes', 'nullable', 'string'],
        ]);

        $appointment = Appointment::create([
            'patient_id' => $user->id,
            'reason' => $validated['reason'],
            'patient_note' => $validated['patient_note'] ?? null,
            'status' => Appointment::STATUS_REQUESTED,
        ]);

        return response()->json([
            'message' => 'Appointment request created successfully.',
            'appointment' => new AppointmentResource($appointment->load(['patient', 'doctor', 'nurse'])),
        ], 201);
    }

    private function storeNurseAppointment(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'integer', $this->userRoleRule(User::ROLE_PATIENT)],
            'doctor_id' => ['sometimes', 'nullable', 'integer', $this->userRoleRule(User::ROLE_DOCTOR)],
            'nurse_id' => ['sometimes', 'nullable', 'integer', $this->userRoleRule(User::ROLE_NURSE)],
            'scheduled_at' => ['sometimes', 'nullable', 'date'],
            'reason' => ['required', 'string'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'patient_note' => ['sometimes', 'nullable', 'string'],
            'nurse_note' => ['sometimes', 'nullable', 'string'],
        ]);

        $validated['nurse_id'] ??= $user->id;
        $validated['status'] ??= isset($validated['doctor_id'], $validated['scheduled_at'])
            ? Appointment::STATUS_SCHEDULED
            : Appointment::STATUS_REQUESTED;

        $appointment = Appointment::create($validated);

        return response()->json([
            'message' => 'Appointment created successfully.',
            'appointment' => new AppointmentResource($appointment->load(['patient', 'doctor', 'nurse'])),
        ], 201);
    }

    private function updatePatientAppointment(Request $request, Appointment $appointment, User $user): JsonResponse
    {
        if ($appointment->patient_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (in_array($appointment->status, [Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED], true)) {
            return response()->json([
                'message' => 'Completed or cancelled appointments cannot be updated by the patient.',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => ['sometimes', 'string'],
            'patient_note' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::in([Appointment::STATUS_CANCELLED])],
        ]);

        if ($validated === []) {
            return response()->json([
                'message' => 'Nothing to update.',
                'appointment' => new AppointmentResource($appointment->load(['patient', 'doctor', 'nurse'])),
            ]);
        }

        $appointment->update($validated);

        return response()->json([
            'message' => 'Appointment updated successfully.',
            'appointment' => new AppointmentResource($appointment->refresh()->load(['patient', 'doctor', 'nurse'])),
        ]);
    }

    private function updateNurseAppointment(Request $request, Appointment $appointment): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['sometimes', 'nullable', 'integer', $this->userRoleRule(User::ROLE_DOCTOR)],
            'nurse_id' => ['sometimes', 'nullable', 'integer', $this->userRoleRule(User::ROLE_NURSE)],
            'scheduled_at' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'nurse_note' => ['sometimes', 'nullable', 'string'],
        ]);

        if ($validated === []) {
            return response()->json([
                'message' => 'Nothing to update.',
                'appointment' => new AppointmentResource($appointment->load(['patient', 'doctor', 'nurse'])),
            ]);
        }

        $appointment->update($validated);

        return response()->json([
            'message' => 'Appointment updated successfully.',
            'appointment' => new AppointmentResource($appointment->refresh()->load(['patient', 'doctor', 'nurse'])),
        ]);
    }

    private function canView(User $user, Appointment $appointment): bool
    {
        return match ($user->role) {
            User::ROLE_PATIENT => $appointment->patient_id === $user->id,
            User::ROLE_DOCTOR => $appointment->doctor_id === $user->id,
            User::ROLE_NURSE => true,
            default => false,
        };
    }

    private function userRoleRule(string $role): Exists
    {
        return Rule::exists('users', 'id')->where('role', $role);
    }
}
