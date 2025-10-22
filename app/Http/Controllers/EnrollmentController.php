<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Offering;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnrollmentController extends Controller
{
    // GET /enrollments
    public function index()
    {
        return response()->json(
            Enrollment::with(['student.user', 'offering.course', 'offering.teacher.user', 'offering.branch'])
                ->paginate(10)
        );
    }

    // POST /enrollments
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'  => 'required|exists:students,id',
            'offering_id' => 'required|exists:offerings,id',
            'status'      => 'nullable|in:activa,retirada,finalizada',
        ]);

        // Evitar duplicados
        $exists = Enrollment::where('student_id', $data['student_id'])
            ->where('offering_id', $data['offering_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'El estudiante ya está inscrito en esta oferta'], 422);
        }

        // Crear inscripción
        $enrollment = Enrollment::create([
            'student_id'  => $data['student_id'],
            'offering_id' => $data['offering_id'],
            'status'      => $data['status'] ?? 'activa',
            'fecha'       => now(),
        ]);

        // 🔹 Reducir cupo si es válido
        $offering = Offering::find($data['offering_id']);
        if ($offering && $offering->cupo > 0) {
            $offering->decrement('cupo');
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Inscripción registrada correctamente',
            'enrollment' => $enrollment->load('student.user', 'offering.course', 'offering.branch'),
        ], Response::HTTP_CREATED);
    }

    // GET /enrollments/{id}
    public function show(Enrollment $enrollment)
    {
        return response()->json(
            $enrollment->load(['student.user', 'offering.course', 'offering.teacher.user', 'offering.branch'])
        );
    }

    // PUT/PATCH /enrollments/{id}
    public function update(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate([
            'status' => 'required|in:activa,retirada,finalizada',
        ]);

        $oldStatus = $enrollment->status;
        $enrollment->update($data);

        // 🔹 Manejar cupos según cambio de estado
        $offering = Offering::find($enrollment->offering_id);

        if ($offering) {
            // Si pasa de activa → retirada/finalizada → liberar cupo
            if (in_array($data['status'], ['retirada', 'finalizada']) && $oldStatus === 'activa') {
                $offering->increment('cupo');
            }
            // Si pasa de retirada/finalizada → activa → ocupar cupo
            elseif ($data['status'] === 'activa' && in_array($oldStatus, ['retirada', 'finalizada']) && $offering->cupo > 0) {
                $offering->decrement('cupo');
            }
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Estado de la inscripción actualizado correctamente',
            'enrollment' => $enrollment->load('student.user', 'offering.course', 'offering.branch'),
        ]);
    }

    // DELETE /enrollments/{id}
    public function destroy(Enrollment $enrollment)
    {
        // 🔹 Restaurar cupo antes de eliminar
        $offering = Offering::find($enrollment->offering_id);
        if ($offering) {
            $offering->increment('cupo');
        }

        $enrollment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inscripción eliminada correctamente',
        ], Response::HTTP_OK);
    }
}
