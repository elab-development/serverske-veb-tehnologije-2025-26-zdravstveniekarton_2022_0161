<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const ROLES = [
        User::ROLE_PATIENT,
        User::ROLE_NURSE,
        User::ROLE_DOCTOR,
        User::ROLE_ADMIN,
    ];

    public function index(Request $request): JsonResponse
    {
        if (! $this->isAdmin($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'role' => ['sometimes', Rule::in(self::ROLES)],
        ]);

        $query = User::query()
            ->with('medicalRecord')
            ->orderBy('name');

        if (isset($validated['role'])) {
            $query->where('role', $validated['role']);
        }

        $users = $query->get();

        return response()->json([
            'count' => $users->count(),
            'filters' => $request->only('role'),
            'users' => UserResource::collection($users),
        ]);
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        if (! $this->isAdmin($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        $oldRole = $user->role;
        $newRole = $validated['role'];

        if ($oldRole === $newRole) {
            return response()->json([
                'message' => 'User already has this role.',
                'user' => new UserResource($user->load('medicalRecord')),
            ]);
        }

        DB::transaction(function () use ($user, $newRole, $oldRole): void {
            if ($oldRole === User::ROLE_PATIENT && $newRole !== User::ROLE_PATIENT) {
                $user->medicalRecord?->delete();
                $user->patientAppointments()->delete();
            }

            $user->update([
                'role' => $newRole,
            ]);

            if ($newRole === User::ROLE_PATIENT && ! $user->medicalRecord()->exists()) {
                $user->medicalRecord()->create();
            }
        });

        return response()->json([
            'message' => 'User role updated successfully.',
            'user' => new UserResource($user->refresh()->load('medicalRecord')),
        ]);
    }

    private function isAdmin(Request $request): bool
    {
        return $request->user()?->role === User::ROLE_ADMIN;
    }
}
