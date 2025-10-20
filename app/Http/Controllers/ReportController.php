<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Grade;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /** Reporte de estudiantes por fecha de inscripción */
    public function studentsByDate(Request $request)
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        $query = Enrollment::with('student')
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]));

        return response()->json($query->get());
    }

    /** Reporte de estudiantes por grado */
    public function studentsByGrade($grade)
    {
        $students = Student::where('grade', $grade)->with('branch')->get();
        return response()->json($students);
    }

    /** Reporte de notas por curso */
    public function gradesByCourse($courseId)
    {
        $grades = Grade::with(['enrollment.student'])
            ->whereHas('enrollment.offering', fn($q) => $q->where('course_id', $courseId))
            ->get();

        return response()->json($grades);
    }

    /** Exportación de estudiantes (ejemplo simple en JSON, luego se puede pasar a Excel/PDF) */
    public function exportStudents()
    {
        $students = Student::with('branch')->get();
        return response()->json([
            'export' => 'students',
            'count'  => $students->count(),
            'data'   => $students
        ]);
    }

    /** Exportación de calificaciones */
    public function exportGrades()
    {
        $grades = Grade::with(['enrollment.student','enrollment.offering.course'])->get();
        return response()->json([
            'export' => 'grades',
            'count'  => $grades->count(),
            'data'   => $grades
        ]);
    }
}
