<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EstadisticasExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        return DB::table('grades')
            ->join('enrollments', 'grades.enrollment_id', '=', 'enrollments.id')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->select('students.grade as Grado', DB::raw('AVG(grades.total) as Promedio_General'))
            ->groupBy('students.grade')
            ->orderBy('students.grade')
            ->get()
            ->map(fn($r) => [
                'Grado' => $r->Grado,
                'Promedio General' => round($r->Promedio_General, 2)
            ]);
    }

    public function headings(): array
    {
        return ['Grado', 'Promedio General'];
    }
}
