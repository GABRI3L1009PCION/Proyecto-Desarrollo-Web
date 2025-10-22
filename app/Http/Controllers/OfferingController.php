<?php

namespace App\Http\Controllers;

use App\Models\Offering;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OfferingController extends Controller
{
    // GET /offerings
    public function index()
    {
        return response()->json(
            Offering::with(['course', 'teacher.user', 'branch'])
                ->orderByDesc('anio')
                ->paginate(10)
        );
    }

    // POST /offerings
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id'  => 'required|exists:courses,id',
            'branch_id'  => 'required|exists:branches,id',
            'teacher_id' => 'required|exists:teachers,id',
            'anio'       => 'required|integer|min:2000',
            'ciclo'      => 'required|string|max:10',
            'horario'    => 'nullable|string|max:255',
            'cupo'       => 'required|integer|min:1',
            'grade'      => 'required|in:Novatos,Expertos',
            'level'      => 'required|in:Principiantes I,Principiantes II,Avanzados I,Avanzados II',
        ]);

        // 🔹 Evitar duplicados (mismo curso-docente-sucursal-grado-nivel-año-ciclo)
        $exists = Offering::where('course_id', $validated['course_id'])
            ->where('teacher_id', $validated['teacher_id'])
            ->where('branch_id', $validated['branch_id'])
            ->where('grade', $validated['grade'])
            ->where('level', $validated['level'])
            ->where('anio', $validated['anio'])
            ->where('ciclo', $validated['ciclo'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ Ya existe una oferta con los mismos parámetros.'
            ], 422);
        }

        $offering = Offering::create($validated);

        return response()->json([
            'success'  => true,
            'message'  => '✅ Oferta creada correctamente.',
            'offering' => $offering->load(['course', 'teacher.user', 'branch'])
        ], Response::HTTP_CREATED);
    }

    // GET /offerings/{id}
    public function show($id)
    {
        $offering = Offering::with(['course', 'teacher.user', 'branch', 'enrollments.student.user'])->findOrFail($id);

        return response()->json([
            'success'  => true,
            'offering' => $offering
        ]);
    }

    // PUT/PATCH /offerings/{id}
    public function update(Request $request, $id)
    {
        $offering = Offering::findOrFail($id);

        $validated = $request->validate([
            'teacher_id' => 'sometimes|exists:teachers,id',
            'branch_id'  => 'sometimes|exists:branches,id',
            'horario'    => 'nullable|string|max:255',
            'cupo'       => 'sometimes|integer|min:1',
            'anio'       => 'sometimes|integer|min:2000',
            'ciclo'      => 'sometimes|string|max:10',
            'grade'      => 'sometimes|in:Novatos,Expertos',
            'level'      => 'sometimes|in:Principiantes I,Principiantes II,Avanzados I,Avanzados II',
        ]);

        $offering->update($validated);

        return response()->json([
            'success'  => true,
            'message'  => '✏️ Oferta actualizada correctamente.',
            'offering' => $offering->load(['course', 'teacher.user', 'branch'])
        ]);
    }

    // DELETE /offerings/{id}
    public function destroy($id)
    {
        $offering = Offering::withCount('enrollments')->findOrFail($id);

        // 🔸 Evitar eliminar si tiene alumnos inscritos
        if ($offering->enrollments_count > 0) {
            return response()->json([
                'success' => false,
                'message' => '❌ No se puede eliminar una oferta con alumnos inscritos.',
                'count'   => $offering->enrollments_count
            ], 409);
        }

        $offering->delete();

        return response()->json([
            'success' => true,
            'message' => '🗑️ Oferta eliminada correctamente.'
        ], Response::HTTP_OK);
    }
}
