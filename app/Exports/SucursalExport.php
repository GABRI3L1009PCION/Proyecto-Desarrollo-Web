<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SucursalExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $branch_id;

    public function __construct($branch_id = null)
    {
        $this->branch_id = $branch_id;
    }

    public function collection()
    {
        // Si se seleccionó una sucursal específica, listar alumnos
        if ($this->branch_id) {
            return Student::with('branch')
                ->where('branch_id', $this->branch_id)
                ->orderBy('nombres')
                ->get()
                ->map(fn($a) => [
                    'Alumno'   => $a->nombres ?? 'Sin nombre',
                    'Grado'    => $a->grade ?? 'N/A',
                    'Nivel'    => $a->level ?? 'N/A',
                    'Sucursal' => $a->branch->nombre ?? 'Sin asignar',
                ]);
        }

        // Si no se selecciona sucursal, mostrar totales
        return Student::join('branches', 'students.branch_id', '=', 'branches.id')
            ->select('branches.nombre as Sucursal', DB::raw('COUNT(students.id) as Total_Alumnos'))
            ->groupBy('branches.nombre')
            ->orderBy('branches.nombre')
            ->get();
    }

    public function headings(): array
    {
        // Cabecera distinta según si se filtra o no
        if ($this->branch_id) {
            return ['Alumno', 'Grado', 'Nivel', 'Sucursal'];
        }

        return ['Sucursal', 'Total de Alumnos'];
    }
}
