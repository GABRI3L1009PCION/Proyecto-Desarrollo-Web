<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    /** ======================================================
     *  📆 Reporte: Estudiantes por fecha de inscripción
     * ====================================================== */
    public function studentsByDate(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $from = $request->input('from') ?? now()->subMonth()->startOfDay();
        $to   = $request->input('to')   ?? now()->endOfDay();

        $inscripciones = Enrollment::with(['student.user', 'offering.course', 'offering.branch'])
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "📋 Reporte de inscripciones del $from al $to",
            'count'   => $inscripciones->count(),
            'data'    => $inscripciones
        ]);
    }

    /** ======================================================
     *  🎓 Reporte: Estudiantes por grado
     * ====================================================== */
    public function studentsByGrade(Request $request)
    {
        $request->validate([
            'grade' => 'required|in:Novatos,Expertos'
        ]);

        $students = Student::with(['branch', 'user'])
            ->where('grade', $request->grade)
            ->orderBy('level')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "🎓 Estudiantes del grado {$request->grade}",
            'count'   => $students->count(),
            'data'    => $students
        ]);
    }

    /** ======================================================
     *  🧾 Reporte: Calificaciones por curso
     * ====================================================== */
    public function gradesByCourse($courseId)
    {
        $grades = Grade::with(['enrollment.student.user', 'enrollment.offering.course'])
            ->whereHas('enrollment.offering', fn($q) => $q->where('course_id', $courseId))
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "🧾 Calificaciones del curso #$courseId",
            'count'   => $grades->count(),
            'data'    => $grades
        ]);
    }

    /** ======================================================
     *  📤 Exportación de estudiantes (versión JSON)
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
     *  📤 Exportación de calificaciones (versión JSON)
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
