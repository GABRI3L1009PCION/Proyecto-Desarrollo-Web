<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Course;

class ReportController extends Controller
{
    /* ======================================================
       📆 REPORTE: ESTUDIANTES POR FECHA DE INSCRIPCIÓN
    ====================================================== */
    public function studentsByDate(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->subMonth()->startOfDay();
        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $inscripciones = Enrollment::with(['student.user', 'offering.course', 'offering.branch'])
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "📋 Reporte de inscripciones del {$from->format('Y-m-d')} al {$to->format('Y-m-d')}",
            'count'   => $inscripciones->count(),
            'data'    => $inscripciones,
        ], Response::HTTP_OK);
    }

    /* ======================================================
       🎓 REPORTE: ESTUDIANTES POR GRADO Y NIVEL
    ====================================================== */
    public function studentsByGrade(Request $request)
    {
        $request->validate([
            'grade' => 'nullable|in:Novatos,Expertos'
        ]);

        $query = Student::select('grade', 'level', DB::raw('COUNT(*) as total'))
            ->groupBy('grade', 'level')
            ->orderBy('grade')
            ->orderBy('level');

        if ($request->filled('grade')) {
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
            'data'    => $reporte,
        ], Response::HTTP_OK);
    }

    /* ======================================================
       🧾 REPORTE: CALIFICACIONES POR CURSO
    ====================================================== */
    public function gradesByCourse(Request $request, $courseId = null)
    {
        $courseId = $courseId ?? $request->input('course_id');

        if (!$courseId) {
            return response()->json([
                'success' => false,
                'message' => 'Se requiere el ID del curso.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $query = Grade::with(['enrollment.student.user', 'enrollment.offering.course'])
            ->whereHas('enrollment.offering', fn($q) => $q->where('course_id', $courseId));

        if ($request->filled('from')) {
            $query->whereDate('updated_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('updated_at', '<=', $request->input('to'));
        }

        $grades = $query->orderByDesc('updated_at')->get();
        $courseName = Course::find($courseId)->nombre ?? "#$courseId";

        return response()->json([
            'success' => true,
            'message' => "🧾 Calificaciones del curso {$courseName}",
            'count'   => $grades->count(),
            'data'    => $grades,
        ], Response::HTTP_OK);
    }

    /* ======================================================
       🏢 REPORTE: ESTUDIANTES POR SUCURSAL
    ====================================================== */
    public function studentsByBranch(Request $request)
    {
        $students = Student::with('branch')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->orderBy('branch_id')
            ->get();

        $message = $request->branch_id
            ? "🏢 Estudiantes de la sucursal seleccionada"
            : "🏢 Listado general de estudiantes por sucursal";

        return response()->json([
            'success' => true,
            'message' => $message,
            'count'   => $students->count(),
            'data'    => $students,
        ], Response::HTTP_OK);
    }

    /* ======================================================
       📊 REPORTE: ESTADÍSTICAS POR GRADO (PROMEDIOS)
    ====================================================== */
    public function statsByGrade()
    {
        $stats = DB::table('grades')
            ->join('enrollments', 'grades.enrollment_id', '=', 'enrollments.id')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->select('students.grade', DB::raw('AVG(grades.total) as promedio'))
            ->groupBy('students.grade')
            ->orderBy('students.grade')
            ->get();

        return response()->json([
            'success' => true,
            'message' => '📊 Promedio general por grado',
            'data'    => $stats,
        ], Response::HTTP_OK);
    }

    /* ======================================================
       📤 EXPORTACIONES EN FORMATO JSON (NO EXCEL)
    ====================================================== */
    public function exportStudents()
    {
        $students = Student::with(['branch', 'user'])
            ->orderBy('branch_id')
            ->get();

        return response()->json([
            'success' => true,
            'export'  => 'students',
            'count'   => $students->count(),
            'data'    => $students,
        ], Response::HTTP_OK);
    }

    public function exportGrades()
    {
        $grades = Grade::with(['enrollment.student.user', 'enrollment.offering.course'])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'export'  => 'grades',
            'count'   => $grades->count(),
            'data'    => $grades,
        ], Response::HTTP_OK);
    }
}
