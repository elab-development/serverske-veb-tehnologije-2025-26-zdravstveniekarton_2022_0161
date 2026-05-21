<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    // GET /api/appointments (Sa paginacijom i filtriranjem za visu ocenu)
    public function index(Request $request)
    {
        $query = Appointment::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(5), 200);
    }

    // POST /api/appointments (Kreiranje)
    public function store(Request $request)
    {
        $fields = $request->validate([
            'patient_id' => 'required|integer',
            'doctor_id' => 'required|integer',
            'appointment_date' => 'required|date',
        ]);

        $appointment = Appointment::create($fields);

        return response()->json($appointment, 201);
    }

    // GET /api/appointments/{id} (Prikaz jednog)
    public function show($id)
    {
        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json(['message' => 'Pregled nije pronadjen'], 404);
        }

        return response()->json($appointment, 200);
    }

    // PUT/PATCH /api/appointments/{id} (Izmena)
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Pregled nije pronadjen'], 404);
        }

        $appointment->update($request->all());
        return response()->json($appointment, 200);
    }

    // DELETE /api/appointments/{id} (Brisanje)
    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Pregled nije pronadjen'], 404);
        }

        $appointment->delete();
        return response()->json(['message' => 'Pregled uspesno obrisan'], 200);
    }
}