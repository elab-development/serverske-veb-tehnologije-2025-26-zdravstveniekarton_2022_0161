<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    /**
     * GET /api/appointments (Prikaz svih pregleda)
     */
    public function index()
    {
        $appointments = Appointment::with(['doctor', 'patient'])->get();
        
        return response()->json([
            'success' => true,
            'count' => $appointments->count(),
            'data' => $appointments
        ], 200);
    }

    /**
     * POST /api/appointments (Kreiranje novog pregleda - npr. sestra ili doktor zakazuju)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:users,id',
            'patient_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date|after:now',
            'symptoms' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $appointment = Appointment::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Pregled uspešno zakazan.',
            'data' => $appointment
        ], 201);
    }

    /**
     * GET /api/appointments/{id} (Prikaz pojedinačnog pregleda)
     */
    public function show($id)
    {
        $appointment = Appointment::with(['doctor', 'patient'])->find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Pregled nije pronađen.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $appointment
        ], 200);
    }

    /**
     * PUT/PATCH /api/appointments/{id} (Izmena podataka o pregledu - npr. doktor upisuje dijagnozu)
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Pregled nije pronađen.'
            ], 404);
        }

        // Ovde doktor može da ažurira dijagnozu i status
        $appointment->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Pregled uspešno ažuriran.',
            'data' => $appointment
        ], 200);
    }

    /**
     * DELETE /api/appointments/{id} (Brisanje/Otkazivanje pregleda)
     */
    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Pregled nije pronađen.'
            ], 404);
        }

        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pregled uspešno obrisan iz sistema.'
        ], 200);
    }
}