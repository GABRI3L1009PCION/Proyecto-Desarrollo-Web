<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GradoNivelExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $tipo;

    public function __construct($tipo = 'grado_nivel')
    {
        $this->tipo = $tipo;
    }

    public function collection()
    {
        // Dependiendo del tipo que venga del Blade
        if ($this->tipo === 'grado') {
            return Student::select('grade', DB::raw('COUNT(*) as total'))
                ->groupBy('grade')
                ->orderBy('grade')
                ->get();
        }

        if ($this->tipo === 'nivel') {
            return Student::select('level', DB::raw('COUNT(*) as total'))
                ->groupBy('level')
                ->orderBy('level')
                ->get();
        }

        // Por defecto, exportar por grado y nivel
        return Student::select('grade', 'level', DB::raw('COUNT(*) as total'))
            ->groupBy('grade', 'level')
            ->orderBy('grade')
            ->orderBy('level')
            ->get();
    }

    public function headings(): array
    {
        if ($this->tipo === 'grado') return ['Grado', 'Total de Alumnos'];
        if ($this->tipo === 'nivel') return ['Nivel', 'Total de Alumnos'];
        return ['Grado', 'Nivel', 'Total de Alumnos'];
    }
}
