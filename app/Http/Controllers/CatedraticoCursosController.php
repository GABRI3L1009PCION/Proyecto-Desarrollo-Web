<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\Offering;
use App\Models\Enrollment;

class CatedraticoCursosController extends Controller
{
    /**
     * Mostrar los cursos asignados al catedrático.
     */
    public function index()
    {
        $user = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return view('Catedratico.sin_perfil', compact('user'));
        }

        // 🔹 Cargar cursos con sus relaciones completas
        $cursos = Offering::with([
            'course',
            'branch',
            'enrollments.student.user',
        ])
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('anio')
            ->get();

        return view('Catedratico.mis_cursos', compact('teacher', 'cursos'));
    }

    /**
     * Mostrar los alumnos inscritos en un curso dentro del mismo modal.
     * (Esta función no devuelve vistas ni JSON, solo datos en memoria).
     */
    public function alumnos($cursoId)
    {
        $curso = Offering::with(['course', 'enrollments.student.user'])
            ->findOrFail($cursoId);

        $alumnos = $curso->enrollments->map(function ($inscripcion) {
            return [
                'nombre'   => $inscripcion->student->user->name ?? 'Sin nombre',
                'telefono' => $inscripcion->student->telefono ?? '—',
                'grado'    => $inscripcion->offering->grade ?? '—',
                'nivel'    => $inscripcion->offering->level ?? '—',
            ];
        });

        return $alumnos;
    }
}
