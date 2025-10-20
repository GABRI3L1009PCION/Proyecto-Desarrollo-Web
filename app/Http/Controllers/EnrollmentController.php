<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnrollmentController extends Controller
{
    // GET /enrollments
    public function index()
    {
        return response()->json(
            Enrollment::with(['student.user', 'offering.course', 'offering.teacher.user'])
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

        // Validar que no exista duplicado (unique en migración lo previene, pero validamos igual)
        $exists = Enrollment::where('student_id', $data['student_id'])
            ->where('offering_id', $data['offering_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'El estudiante ya está inscrito en esta oferta'], 422);
        }

        $enrollment = Enrollment::create([
            'student_id'  => $data['student_id'],
            'offering_id' => $data['offering_id'],
            'status'      => $data['status'] ?? 'activa',
            'fecha'       => now(),
        ]);

        return response()->json($enrollment->load('student.user', 'offering.course'), Response::HTTP_CREATED);
    }

    // GET /enrollments/{id}
    public function show(Enrollment $enrollment)
    {
        return response()->json(
            $enrollment->load(['student.user', 'offering.course', 'offering.teacher.user'])
        );
    }

    // PUT/PATCH /enrollments/{id}
    public function update(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate([
            'status' => 'required|in:activa,retirada,finalizada',
        ]);

        $enrollment->update($data);

        return response()->json($enrollment);
    }

    // DELETE /enrollments/{id}
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
