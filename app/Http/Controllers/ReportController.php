<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB; // Necesario para la agregación en studentsByGrade
use Carbon\Carbon; // Necesario para manipular las fechas en studentsByDate

class ReportController extends Controller
{
    /** ======================================================
     * 📆 Reporte: Estudiantes por fecha de inscripción
     * ====================================================== */
    public function studentsByDate(Request $request)
    {
        // Se corrige la validación para aceptar fechas completas o parciales
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        // Se utilizan los inputs 'from' y 'to' si existen, si no, se usan valores por defecto
        $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : now()->subMonth()->startOfDay();
        $to   = $request->input('to') ? Carbon::parse($request->input('to'))->endOfDay() : now()->endOfDay();

        $inscripciones = Enrollment::with(['student.user', 'offering.course', 'offering.branch'])
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "📋 Reporte de inscripciones del {$from->format('Y-m-d')} al {$to->format('Y-m-d')}",
            'count'   => $inscripciones->count(),
            'data'    => $inscripciones
        ]);
    }

    /** ======================================================
     * 🎓 Reporte: Estudiantes por grado (CON AGREGACIÓN)
     * * Devuelve el conteo de estudiantes por 'grade' y 'level'.
     * ====================================================== */
    public function studentsByGrade(Request $request)
    {
        $request->validate([
            'grade' => 'nullable|in:Novatos,Expertos'
        ]);

        $query = Student::select('grade', 'level', DB::raw('COUNT(*) as total'))
            ->groupBy('grade', 'level')
            ->orderBy('grade')
            ->orderBy('level');

        if ($request->has('grade') && $request->grade) {
            $query->where('grade', $request->grade);
        }

        $reporte = $query->get();

        $message = $request->grade
            ? "🎓 Estudiantes del grado {$request->grade}"
            : "🎓 Reporte de estudiantes por grado y nivel";

        return response()->json([
            'success' => true,
            'message' => $message,
            'count'   => $reporte->sum('total'),
            'data'    => $reporte
        ]);
    }

    /** ======================================================
     * 🧾 Reporte: Calificaciones por curso
     * * Acepta course_id y opcionalmente rangos de fechas de actualización.
     * ====================================================== */
    public function gradesByCourse(Request $request, $courseId = null)
    {
        $courseId = $courseId ?? $request->input('course_id');

        if (!$courseId) {
            return response()->json(['success' => false, 'message' => 'Se requiere el ID del curso.'], Response::HTTP_BAD_REQUEST);
        }

        $query = Grade::with(['enrollment.student.user', 'enrollment.offering.course'])
            ->whereHas('enrollment.offering', fn($q) => $q->where('course_id', $courseId));

        // Filtro por rango de fechas (updated_at)
        if ($request->has('from')) {
            $query->whereDate('updated_at', '>=', $request->input('from'));
        }
        if ($request->has('to')) {
            $query->whereDate('updated_at', '<=', $request->input('to'));
        }

        $grades = $query->orderByDesc('updated_at')->get();
        // NOTA: Para que esta línea funcione, Course debe estar importado y la ruta debe ser correcta
        $courseName = Course::find($courseId)->nombre ?? "#$courseId";

        return response()->json([
            'success' => true,
            'message' => "🧾 Calificaciones del curso {$courseName}",
            'count'   => $grades->count(),
            'data'    => $grades
        ], Response::HTTP_OK);
    }

    /** ======================================================
     * 📤 Exportación de estudiantes (versión JSON)
     * ====================================================== */
    public function exportStudents()
    {
        $students = Student::with(['branch', 'user'])
            ->orderBy('branch_id')
            ->get();

        return response()->json([
            'success' => true,
            'export'  => 'students',
            'count'   => $students->count(),
            'data'    => $students
        ], Response::HTTP_OK);
    }

    /** ======================================================
     * 📤 Exportación de calificaciones (versión JSON)
     * ====================================================== */
    public function exportGrades()
    {
        $grades = Grade::with(['enrollment.student.user', 'enrollment.offering.course'])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'export'  => 'grades',
            'count'   => $grades->count(),
            'data'    => $grades
        ], Response::HTTP_OK);
    }
}
