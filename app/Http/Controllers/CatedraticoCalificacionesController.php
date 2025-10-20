<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\Offering;
use App\Models\Enrollment;
use App\Models\Grade;

class CatedraticoCalificacionesController extends Controller
{
    /**
     * Mostrar los cursos y alumnos del catedrático.
     */
    public function index()
    {
        $user = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return view('Catedratico.sin_perfil', compact('user'));
        }

        // Cursos del catedrático con sus alumnos y notas
        $cursos = Offering::with([
            'course',
            'enrollments.student.user',
            'enrollments.grade'
        ])
            ->where('teacher_id', $teacher->id)
            ->orderBy('anio', 'desc')
            ->get();

        return view('Catedratico.calificaciones', compact('teacher', 'cursos'));
    }

    /**
     * Guardar o actualizar calificaciones.
     */
    public function guardar(Request $request)
    {
        $datos = $request->input('notas', []);

        foreach ($datos as $enrollmentId => $valores) {
            $p1 = $valores['parcial1'] ?? null;
            $p2 = $valores['parcial2'] ?? null;
            $final = $valores['final'] ?? null;

            // === VALIDACIONES DE SECUENCIA ===
            if ($p2 !== null && ($p1 === null || $p1 === '')) {
                return back()->with('error', '⚠ No puedes asignar nota al Segundo Parcial sin tener nota en el Primero.');
            }
            if ($final !== null && ($p1 === null || $p2 === null || $p1 === '' || $p2 === '')) {
                return back()->with('error', '⚠ No puedes asignar nota al Examen Final sin completar los parciales anteriores.');
            }

            // === VALIDACIONES DE RANGO ===
            if ($p1 !== null && ($p1 < 0 || $p1 > 30)) {
                return back()->with('error', '❌ La nota del Primer Parcial debe ser entre 0 y 30.');
            }
            if ($p2 !== null && ($p2 < 0 || $p2 > 30)) {
                return back()->with('error', '❌ La nota del Segundo Parcial debe ser entre 0 y 30.');
            }
            if ($final !== null && ($final < 0 || $final > 40)) {
                return back()->with('error', '❌ La nota del Examen Final debe ser entre 0 y 40.');
            }

            // === CÁLCULO DE TOTAL Y ESTADO ===
            $total = ($p1 ?? 0) + ($p2 ?? 0) + ($final ?? 0);

            if ($total >= 70) {
                $estado = 'Aprobado';
            } elseif ($total >= 60) {
                $estado = 'Recuperación';
            } else {
                $estado = 'Reprobado';
            }

            // === GUARDAR O ACTUALIZAR NOTAS ===
            Grade::updateOrCreate(
                ['enrollment_id' => $enrollmentId],
                [
                    'parcial1' => $p1,
                    'parcial2' => $p2,
                    'final'    => $final,
                    'total'    => $total,
                    'estado'   => $estado,
                ]
            );
        }

        return back()->with('success', '✅ Calificaciones guardadas correctamente.');
    }
}
