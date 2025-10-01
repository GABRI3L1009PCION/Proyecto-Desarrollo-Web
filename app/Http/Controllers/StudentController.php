<?php
namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /** Listar estudiantes */
    public function index()
    {
        return Student::with(['branch', 'enrollments'])
            ->latest('id')
            ->get();
    }

    /** Crear estudiante */
    public function store(Request $r)
    {
        $data = $r->validate([
            'branch_id'        => 'required|exists:branches,id',
            'nombres'          => 'required|string|max:120',
            'telefono'         => 'nullable|string|max:30',
            'fecha_nacimiento' => 'nullable|date',
            'grade'            => 'required|in:Novatos,Expertos',
            'level'            => 'required|in:Principiantes I,Principiantes II,Avanzados I,Avanzados II',
        ]);

        return response()->json(Student::create($data), 201);
    }

    /** Ver un estudiante */
    public function show(Student $student)
    {
        return $student->load(['branch', 'enrollments']);
    }

    /** Actualizar estudiante */
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
        return $student->load('branch');
    }

    /** Eliminar estudiante */
    public function destroy(Student $student)
    {
        $student->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}
