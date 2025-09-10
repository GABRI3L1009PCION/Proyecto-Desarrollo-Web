<?php
// app/Http/Controllers/AdminPanelController.php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Course;
use App\Models\Offering;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminPanelController extends Controller
{
    public function index()
    {
        // ===== KPIs =====
        $kpi = [
            'branches'  => Branch::count(),
            'courses'   => Course::count(),
            'offerings' => Offering::count(),
            'students'  => Student::count(),
        ];

        // ===== Sucursales con conteos =====
        // (Branch debe tener los relations ->students() y ->offerings())
        $branches = Branch::withCount(['students', 'offerings'])
            ->orderBy('nombre')
            ->get();

        // ===== Charts =====

        // A) Inscripciones últimos 12 meses
        $start = Carbon::now()->startOfMonth()->subMonths(11);

        $rows = Enrollment::selectRaw('DATE_FORMAT(created_at, "%Y-%m-01") as month, COUNT(*) as total')
            ->where('created_at', '>=', $start)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $data   = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = Carbon::now()->startOfMonth()->subMonths($i);
            $labels[] = $m->locale('es')->isoFormat('MMM YY');  // Ej: "sep 25"
            $total = optional($rows->firstWhere('month', $m->format('Y-m-01')))->total ?? 0;
            $data[]  = (int) $total;
        }
        $enrollments = [
            'labels' => $labels,
            'data'   => $data,
        ];

        // B) Alumnos por sucursal (Top 5)
        $topBranches = Branch::withCount('students')
            ->orderByDesc('students_count')
            ->limit(5)
            ->get();

        $studentsByBranch = [
            'labels' => $topBranches->pluck('nombre')->toArray(),
            'data'   => $topBranches->pluck('students_count')->map(fn ($v) => (int) $v)->toArray(),
        ];

        // C) Cursos con más inscripciones (Top 5)
        $popular = Course::select('courses.nombre', DB::raw('COUNT(enrollments.id) as total'))
            ->join('offerings', 'offerings.course_id', '=', 'courses.id')
            ->leftJoin('enrollments', 'enrollments.offering_id', '=', 'offerings.id')
            ->groupBy('courses.id', 'courses.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $popularCourses = [
            'labels' => $popular->pluck('nombre')->toArray(),
            'data'   => $popular->pluck('total')->map(fn ($v) => (int) $v)->toArray(),
        ];

        $charts = compact('enrollments', 'studentsByBranch', 'popularCourses');

        return view('Administrador.panel', compact('kpi', 'branches', 'charts'));
    }
}
