<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GradoNivelExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Student::select('grade', 'level', DB::raw('COUNT(*) as total'))
            ->groupBy('grade', 'level')
            ->get();
    }

    public function headings(): array
    {
        return ['Grado', 'Nivel', 'Total de Alumnos'];
    }
}
