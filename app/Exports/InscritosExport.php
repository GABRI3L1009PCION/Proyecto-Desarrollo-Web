<?php

namespace App\Exports;

use App\Models\Enrollment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InscritosExport implements FromCollection, WithHeadings
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
        return Enrollment::with(['student.branch', 'offering.course'])
            ->when($this->inicio, fn($q) => $q->whereDate('fecha', '>=', $this->inicio))
            ->when($this->fin, fn($q) => $q->whereDate('fecha', '<=', $this->fin))
            ->get()
            ->map(function ($e) {
                return [
                    'Alumno'   => $e->student->nombres ?? '',
                    'Curso'    => $e->offering->course->nombre ?? '',
                    'Sucursal' => $e->student->branch->nombre ?? '',
                    'Fecha' => date('d/m/Y', strtotime($e->fecha)),
                    'Estado'   => $e->status,
                ];
            });
    }

    public function headings(): array
    {
        return ['Alumno', 'Curso', 'Sucursal', 'Fecha', 'Estado'];
    }
}
