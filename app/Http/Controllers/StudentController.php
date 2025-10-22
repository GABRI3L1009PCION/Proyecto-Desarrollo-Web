<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StudentController extends Controller
{
    /** ======================================================
     *  📋 Listar todos los estudiantes
     * ====================================================== */
    public function index()
    {
        $students = Student::with(['branch', 'user', 'enrollments.offering.course'])
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => '📋 Lista de estudiantes obtenida correctamente.',
            'count'   => $students->total(),
            'data'    => $students->items(),
            'pagination' => [
                'current_page' => $students->currentPage(),
                'last_page'    => $students->lastPage(),
                'per_page'     => $students->perPage(),
            ]
        ], Response::HTTP_OK);
    }

    /** ======================================================
     *  ➕ Registrar un nuevo estudiante
     * ====================================================== */
    public function store(Request $r)
    {
        $data = $r->validate([
            'user_id'          => 'nullable|exists:users,id|unique:students,user_id',
            'branch_id'        => 'required|exists:branches,id',
            'nombres'          => 'required|string|max:120',
            'telefono'         => 'nullable|string|max:30',
            'fecha_nacimiento' => 'nullable|date',
            'grade'            => 'required|in:Novatos,Expertos',
            'level'            => 'required|in:Principiantes I,Principiantes II,Avanzados I,Avanzados II',
        ]);

        $student = Student::create($data);

        return response()->json([
            'success' => true,
            'message' => '✅ Estudiante registrado correctamente.',
            'data'    => $student->load(['branch', 'user'])
        ], Response::HTTP_CREATED);
    }

    /** ======================================================
     *  👀 Ver detalles de un estudiante
     * ====================================================== */
    public function show(Student $student)
    {
        return response()->json([
            'success' => true,
            'message' => '👀 Detalles del estudiante obtenidos.',
            'data'    => $student->load(['branch', 'user', 'enrollments.offering.course'])
        ]);
    }

    /** ======================================================
     *  ✏️ Actualizar información de un estudiante
     * ====================================================== */
    public function update(Request $r, Student $student)
    {
        $data = $r->validate([
            'branch_id'        => 'sometimes|required|exists:branches,id',
            'nombres'          => 'sometimes|required|string|max:120',
            'telefono'         => 'nullable|string|max:30',
            'fecha_nacimiento' => 'nullable|date',
            'grade'            => 'sometimes|required|in:Novatos,Expertos',
            'level'            => 'sometimes|required|in:Principiantes I,Principiantes II,Avanzados I,Avanzados II',
        ]);

        $student->update($data);

        return response()->json([
            'success' => true,
            'message' => '✏️ Datos del estudiante actualizados correctamente.',
            'data'    => $student->load(['branch', 'user'])
        ], Response::HTTP_OK);
    }

    /** ======================================================
     *  ❌ Eliminar estudiante (solo si no tiene inscripciones)
     * ====================================================== */
    public function destroy(Student $student)
    {
        $student->loadCount('enrollments');

        if ($student->enrollments_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "⚠️ No se puede eliminar este estudiante: tiene {$student->enrollments_count} inscripciones registradas.",
            ], Response::HTTP_CONFLICT);
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => '🗑️ Estudiante eliminado exitosamente.'
        ], Response::HTTP_OK);
    }
}
