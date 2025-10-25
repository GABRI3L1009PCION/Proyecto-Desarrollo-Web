<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EstadisticasExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
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

    public function styles(Worksheet $sheet)
    {
        // Centrar encabezados y darles un poco de estilo
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getStyle('A1:B1')->getAlignment()->setHorizontal('center');

        // Ajustar manualmente un poco más las columnas si lo deseás
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $currentWidth = $sheet->getColumnDimension($col)->getWidth();
            $sheet->getColumnDimension($col)->setWidth($currentWidth + 3);
        }

        return [];
    }
}
