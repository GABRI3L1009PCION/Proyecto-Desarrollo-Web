<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Course;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

// ✅ Exportadores personalizados
use App\Exports\InscritosExport;
use App\Exports\GradoNivelExport;
use App\Exports\NotasExport;
use App\Exports\SucursalExport;

class AdministradorReportesController extends Controller
{
    /**
     * 📊 Vista principal de reportes
     */
    public function index()
    {
        return view('Administrador.reportes_admin');
    }

    /* ======================================================
       📋 REPORTES AJAX
    ====================================================== */

    /**
     * 📆 Alumnos inscritos por fecha
     */
    public function inscritos(Request $request)
    {
        $query = Enrollment::with(['student.user', 'student.branch', 'offering.course'])
            ->when($request->fechaInicio, fn($q) => $q->whereDate('fecha', '>=', $request->fechaInicio))
            ->when($request->fechaFin, fn($q) => $q->whereDate('fecha', '<=', $request->fechaFin))
            ->orderBy('fecha', 'desc')
            ->get();

        $data = $query->map(fn($i) => [
            'id'        => $i->id,
            'alumno'    => $i->student->nombres ?? '',
            'curso'     => $i->offering->course->nombre ?? '',
            'sucursal'  => $i->student->branch->nombre ?? '',
            'fecha'     => date('d/m/Y', strtotime($i->fecha)),
            'estado'    => $i->status,
        ]);

        return response()->json($data);
    }

    /**
     * 🏫 Alumnos por grado
     */
    public function grado()
    {
        $reporte = Student::select('grade', DB::raw('COUNT(*) as total'))
            ->groupBy('grade')
            ->orderBy('grade')
            ->get();

        return response()->json($reporte);
    }

    /**
     * 🏫 Alumnos por nivel
     */
    public function nivel()
    {
        $reporte = Student::select('level', DB::raw('COUNT(*) as total'))
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        return response()->json($reporte);
    }

    /**
     * 🧾 Notas por curso y grado con filtros
     */
    public function notas(Request $request)
    {
        $reporte = Grade::with(['enrollment.offering.course', 'enrollment.student'])
            ->when($request->cursoId, fn($q) =>
            $q->whereHas('enrollment.offering.course', fn($qc) =>
            $qc->where('id', $request->cursoId)
            )
            )
            ->when($request->grado, fn($q) =>
            $q->whereHas('enrollment.student', fn($qs) =>
            $qs->where('grade', $request->grado)
            )
            )
            ->when($request->fechaInicio, fn($q) =>
            $q->whereDate('updated_at', '>=', $request->fechaInicio)
            )
            ->when($request->fechaFin, fn($q) =>
            $q->whereDate('updated_at', '<=', $request->fechaFin)
            )
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($g) => [
                'curso'    => $g->enrollment->offering->course->nombre ?? '',
                'grado'    => $g->enrollment->student->grade ?? '',
                'nivel'    => $g->enrollment->student->level ?? '',
                'alumno'   => $g->enrollment->student->nombres ?? '',
                'parcial1' => $g->parcial1,
                'parcial2' => $g->parcial2,
                'final'    => $g->final,
                'total'    => $g->total,
                'estado'   => $g->estado,
                'fecha'    => optional($g->updated_at)->format('d/m/Y H:i'),
            ]);

        return response()->json($reporte);
    }

    /**
     * 🏢 Alumnos por sucursal (listado)
     */
    public function alumnosPorSucursal(Request $request)
    {
        $alumnos = Student::with('branch')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->get();

        return response()->json($alumnos->map(fn($a) => [
            'alumno'   => $a->nombres,
            'grado'    => $a->grade,
            'nivel'    => $a->level,
            'sucursal' => $a->branch->nombre ?? 'Sin asignar',
        ]));
    }

    /**
     * 📊 Estadísticas por grado (promedios)
     */
    public function estadisticas()
    {
        $reporte = DB::table('grades')
            ->join('enrollments', 'grades.enrollment_id', '=', 'enrollments.id')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->select('students.grade', DB::raw('AVG(grades.total) as promedio'))
            ->groupBy('students.grade')
            ->orderBy('students.grade')
            ->get();

        return response()->json($reporte);
    }

    /* ======================================================
       📤 EXPORTAR REPORTES A EXCEL (FINAL)
    ====================================================== */

    /** 📆 Exportar alumnos inscritos */
    public function exportarInscritos(Request $request)
    {
        return Excel::download(
            new InscritosExport(
                $request->fechaInicio,
                $request->fechaFin
            ),
            'reporte_inscritos.xlsx'
        );
    }

    /** 🏫 Exportar por grado/nivel */
    public function exportarGradoNivel()
    {
        return Excel::download(
            new GradoNivelExport,
            'reporte_grado_nivel.xlsx'
        );
    }

    /** 🧾 Exportar notas (recibe cursoId, grado, fechas) */
    public function exportarNotas(Request $request)
    {
        return Excel::download(
            new NotasExport(
                $request->fechaInicio,
                $request->fechaFin,
                $request->cursoId,
                $request->grado
            ),
            'reporte_notas.xlsx'
        );
    }

    /** 🏢 Exportar alumnos por sucursal (recibe branch_id) */
    public function exportarPorSucursal(Request $request)
    {
        return Excel::download(
            new SucursalExport(
                $request->branch_id
            ),
            'reporte_alumnos_sucursal.xlsx'
        );
    }
    /** 📊 Exportar estadísticas por grado */
    public function exportarEstadisticas()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\EstadisticasExport,
            'reporte_estadisticas_grado.xlsx'
        );
    }

}
