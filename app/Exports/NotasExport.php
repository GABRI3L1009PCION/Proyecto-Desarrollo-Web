<?php

namespace App\Exports;

use App\Models\Grade;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NotasExport implements FromCollection, WithHeadings
{
    protected $inicio;
    protected $fin;

    public function __construct($inicio = null, $fin = null)
    {
        $this->inicio = $inicio;
        $this->fin = $fin;
    }

    public function collection()
    {
        return Grade::with(['enrollment.offering.course', 'enrollment.student'])
            ->when($this->inicio, fn($q) => $q->whereDate('grades.updated_at', '>=', $this->inicio))
            ->when($this->fin, fn($q) => $q->whereDate('grades.updated_at', '<=', $this->fin))
            ->get()
            ->map(function ($g) {
                return [
                    'Curso'   => $g->enrollment->offering->course->nombre ?? '',
                    'Alumno'  => $g->enrollment->student->nombres ?? '',
                    'P1'      => $g->parcial1,
                    'P2'      => $g->parcial2,
                    'Final'   => $g->final,
                    'Total'   => $g->total,
                    'Estado'  => $g->estado,
                ];
            });
    }

    public function headings(): array
    {
        return ['Curso', 'Alumno', 'P1', 'P2', 'Final', 'Total', 'Estado'];
    }
}
