<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Offering;
use App\Models\Grade;

class EstudiantePanelController extends Controller
{
    /**
     * 🏠 Mostrar el panel principal del estudiante.
     */
    public function index()
    {
        $user = auth()->user();

        // Buscar el registro del estudiante vinculado a este usuario
        $student = Student::where('user_id', $user->id)
            ->with(['branch'])
            ->first();

        // Si aún no tiene perfil vinculado, mostrar la vista de "perfil pendiente"
        if (!$student) {
            return view('Estudiante.sin_perfil', compact('user'));
        }

        // Inscripciones del estudiante
        $inscripciones = Enrollment::with(['offering.course', 'grade'])
            ->where('student_id', $student->id)
            ->get();

        // Calcular promedio general
        $promedioGeneral = round($inscripciones->avg(function ($insc) {
            $grade = $insc->grade;
            if (!$grade) return null;
            return ($grade->parcial1 + $grade->parcial2 + $grade->examen_final) / 3;
        }), 2);

        return view('Estudiante.panel', compact('user', 'student', 'inscripciones', 'promedioGeneral'));
    }

    /**
     * 📚 Mostrar los cursos inscritos del estudiante (con filtros).
     */
    public function misCursos(Request $request)
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        // Si no tiene perfil activo
        if (!$student) {
            return view('Estudiante.sin_perfil', compact('user'));
        }

        $query = Enrollment::with([
            'offering.course',
            'offering.teacher.user',
            'offering.branch',
            'grade'
        ])->where('student_id', $student->id);

        // === FILTROS ===
        if ($request->filled('buscar')) {
            $query->whereHas('offering.course', function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%");
            });
        }

        if ($request->filled('nivel')) {
            $query->whereHas('offering', fn($q) => $q->where('level', $request->nivel));
        }

        if ($request->filled('anio')) {
            $query->whereHas('offering', fn($q) => $q->where('anio', $request->anio));
        }

        $inscripciones = $query->orderBy('id', 'desc')->get();

        // ✅ Obtener los años desde offerings, no enrollments
        $anios = Offering::distinct()->pluck('anio')->filter()->sortDesc();

        return view('Estudiante.cursos', compact('user', 'student', 'inscripciones', 'anios'));
    }

    /**
     * 🧾 Mostrar las notas del estudiante por curso.
     */
    public function misNotas()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return view('Estudiante.sin_perfil', compact('user'));
        }

        $notas = Enrollment::with(['offering.course', 'grade'])
            ->where('student_id', $student->id)
            ->get();

        return view('Estudiante.notas', compact('student', 'notas'));
    }

    /**
     * 📊 Reporte de desempeño académico.
     */
    public function miDesempeno()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return view('Estudiante.sin_perfil', compact('user'));
        }

        // Obtener inscripciones con cursos y notas
        $inscripciones = Enrollment::with(['offering.course', 'offering.teacher.user', 'grade'])
            ->where('student_id', $student->id)
            ->get();

        $totalCursos = $inscripciones->count();
        $aprobados = $recuperacion = $reprobados = 0;
        $totalPuntos = 0;

        foreach ($inscripciones as $insc) {
            $grade = $insc->grade;
            if (!$grade) continue;

            $p1 = $grade->parcial1 ?? 0;
            $p2 = $grade->parcial2 ?? 0;
            $final = $grade->final ?? 0;

            // === CÁLCULO DE TOTAL Y ESTADO ===
            $total = ($p1 ?? 0) + ($p2 ?? 0) + ($final ?? 0);
            $totalPuntos += $total;

            if ($total >= 70) {
                $aprobados++;
            } elseif ($total >= 60) {
                $recuperacion++;
            } else {
                $reprobados++;
            }
        }

        // === PROMEDIO GENERAL (en base a 100 puntos por curso) ===
        $promedioGeneral = $totalCursos > 0 ? round($totalPuntos / $totalCursos, 2) : 0;

        return view('Estudiante.desempeno', compact(
            'student',
            'inscripciones',
            'promedioGeneral',
            'totalCursos',
            'aprobados',
            'recuperacion',
            'reprobados'
        ));
    }


    /**
     * 👤 Ver y editar perfil básico del estudiante (solo su información).
     */
    public function perfil()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return view('Estudiante.sin_perfil', compact('user'));
        }

        return view('Estudiante.perfil', compact('user', 'student'));
    }
}
