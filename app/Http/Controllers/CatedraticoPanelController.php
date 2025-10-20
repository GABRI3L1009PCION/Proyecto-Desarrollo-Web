<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\Offering;
use App\Models\Enrollment;
use App\Models\Grade;

class CatedraticoPanelController extends Controller
{
    /**
     * Mostrar el panel principal del catedrático (vista web completa).
     */
    public function index()
    {
        $user = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return view('Catedratico.sin_perfil', compact('user'));
        }

        // Cargar todos los cursos asignados con relaciones completas
        $cursos = Offering::with([
            'course',
            'branch',
            'enrollments.student.user',
            'enrollments.grade'
        ])
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('anio')
            ->get();

        // === Estadísticas ===
        $totalCursos = $cursos->count();
        $totalAlumnos = $cursos->sum(fn($c) => $c->enrollments->count());
        $promedioGeneral = $this->calcularPromedioGeneral($cursos);

        return view('Catedratico.panel', compact(
            'teacher',
            'cursos',
            'totalCursos',
            'totalAlumnos',
            'promedioGeneral'
        ));
    }

    /**
     * Calcular el promedio general de todas las calificaciones del catedrático.
     */
    private function calcularPromedioGeneral($cursos)
    {
        $notas = $cursos->flatMap(fn($c) => $c->enrollments->pluck('grade'));
        $promedio = $notas->filter()->avg(fn($g) =>
            (($g->parcial1 ?? 0) + ($g->parcial2 ?? 0) + ($g->final ?? 0)) / 3
        );

        return $promedio ? number_format($promedio, 2) : '—';
    }
}
