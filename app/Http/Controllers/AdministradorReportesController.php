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

class AdministradorReportesController extends Controller
{
    /**
     * Vista principal de reportes.
     */
    public function index()
    {
        return view('Administrador.reportes_admin');
    }

    /**
     * Obtener alumnos inscritos por fecha (AJAX)
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
     * Reporte por grado y nivel (AJAX)
     */
    public function gradoNivel(Request $request)
    {
        $reporte = Student::with('branch')
            ->select('grade', 'level', DB::raw('COUNT(*) as total'))
            ->groupBy('grade', 'level')
            ->get();

        return response()->json($reporte);
    }

    /**
     * Reporte de notas por curso (AJAX)
     */
    public function notas(Request $request)
    {
        $reporte = Grade::with(['enrollment.offering.course', 'enrollment.student'])
            ->when($request->fechaInicio, fn($q) =>
            $q->whereDate('updated_at', '>=', $request->fechaInicio)
            )
            ->when($request->fechaFin, fn($q) =>
            $q->whereDate('updated_at', '<=', $request->fechaFin)
            )
            ->orderBy('updated_at', 'desc')
            ->select('enrollment_id', 'parcial1', 'parcial2', 'final', 'total', 'estado', 'updated_at')
            ->get()
            ->map(fn($g) => [
                'curso'    => $g->enrollment->offering->course->nombre ?? '',
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

    /* ======================================================
       📤 EXPORTAR REPORTES A EXCEL
    ====================================================== */

    /** Exportar alumnos inscritos */
    public function exportarInscritos(Request $request)
    {
        return Excel::download(
            new InscritosExport($request->fechaInicio, $request->fechaFin),
            'reporte_inscritos.xlsx'
        );
    }

    /** Exportar reporte por grado y nivel */
    public function exportarGradoNivel()
    {
        return Excel::download(
            new GradoNivelExport,
            'reporte_grado_nivel.xlsx'
        );
    }

    /** Exportar reporte de notas */
    public function exportarNotas(Request $request)
    {
        return Excel::download(
            new NotasExport($request->fechaInicio, $request->fechaFin),
            'reporte_notas.xlsx'
        );
    }
}
