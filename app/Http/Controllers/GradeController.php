<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GradeController extends Controller
{
    // GET /grades
    public function index()
    {
        return response()->json(
            Grade::with(['enrollment.student.user', 'enrollment.offering.course'])
                ->paginate(10)
        );
    }

    // POST /grades
    public function store(Request $request)
    {
        $data = $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id|unique:grades,enrollment_id',
            'parcial1'      => 'nullable|numeric|min:0|max:30',
            'parcial2'      => 'nullable|numeric|min:0|max:30',
            'final'         => 'nullable|numeric|min:0|max:40',
            'observaciones' => 'nullable|string|max:255',
        ]);

        // === VALIDACIONES DE SECUENCIA ===
        if (!is_null($data['parcial2'] ?? null) && is_null($data['parcial1'] ?? null)) {
            return response()->json(['message' => '⚠ No puedes asignar nota al Segundo Parcial sin tener nota en el Primero.'], 422);
        }
        if (!is_null($data['final'] ?? null) && (is_null($data['parcial1'] ?? null) || is_null($data['parcial2'] ?? null))) {
            return response()->json(['message' => '⚠ No puedes asignar nota al Examen Final sin completar los parciales anteriores.'], 422);
        }

        // === CÁLCULO DE TOTAL Y ESTADO ===
        $total = ($data['parcial1'] ?? 0) + ($data['parcial2'] ?? 0) + ($data['final'] ?? 0);
        $estado = $total >= 70 ? 'Aprobado' : ($total >= 60 ? 'Recuperación' : 'Reprobado');

        $grade = Grade::create(array_merge($data, compact('total', 'estado')));

        return response()->json([
            'success' => true,
            'message' => '✅ Calificación registrada correctamente.',
            'grade'   => $grade->load('enrollment.student.user', 'enrollment.offering.course'),
        ], Response::HTTP_CREATED);
    }

    // GET /grades/{id}
    public function show(Grade $grade)
    {
        return response()->json(
            $grade->load(['enrollment.student.user', 'enrollment.offering.course'])
        );
    }

    // PUT/PATCH /grades/{id}
    public function update(Request $request, Grade $grade)
    {
        $data = $request->validate([
            'parcial1'      => 'nullable|numeric|min:0|max:30',
            'parcial2'      => 'nullable|numeric|min:0|max:30',
            'final'         => 'nullable|numeric|min:0|max:40',
            'observaciones' => 'nullable|string|max:255',
        ]);

        // === VALIDACIONES DE SECUENCIA ===
        if (!is_null($data['parcial2'] ?? null) && is_null($data['parcial1'] ?? $grade->parcial1)) {
            return response()->json(['message' => '⚠ No puedes asignar nota al Segundo Parcial sin tener nota en el Primero.'], 422);
        }
        if (!is_null($data['final'] ?? null) && (is_null($data['parcial1'] ?? $grade->parcial1) || is_null($data['parcial2'] ?? $grade->parcial2))) {
            return response()->json(['message' => '⚠ No puedes asignar nota al Examen Final sin completar los parciales anteriores.'], 422);
        }

        // === CÁLCULO DE TOTAL Y ESTADO ===
        $p1 = $data['parcial1'] ?? $grade->parcial1 ?? 0;
        $p2 = $data['parcial2'] ?? $grade->parcial2 ?? 0;
        $final = $data['final'] ?? $grade->final ?? 0;
        $total = $p1 + $p2 + $final;
        $estado = $total >= 70 ? 'Aprobado' : ($total >= 60 ? 'Recuperación' : 'Reprobado');

        $grade->update(array_merge($data, compact('total', 'estado')));

        return response()->json([
            'success' => true,
            'message' => '✏️ Calificación actualizada correctamente.',
            'grade'   => $grade->load('enrollment.student.user', 'enrollment.offering.course'),
        ]);
    }

    // DELETE /grades/{id}
    public function destroy(Grade $grade)
    {
        $grade->delete();

        return response()->json([
            'success' => true,
            'message' => '🗑️ Calificación eliminada correctamente.'
        ], Response::HTTP_OK);
    }
}
