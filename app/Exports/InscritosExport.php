<?php

namespace App\Exports;

use App\Models\Enrollment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InscritosExport implements FromCollection, WithHeadings, ShouldAutoSize
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
        $inscritos = Enrollment::with(['student.branch', 'offering.course'])
            ->when($this->inicio, fn($q) => $q->whereDate('fecha', '>=', $this->inicio))
            ->when($this->fin, fn($q) => $q->whereDate('fecha', '<=', $this->fin))
            ->orderBy('fecha', 'desc')
            ->get();

        return $inscritos->map(fn($e) => [
            'Alumno'   => $e->student->nombres ?? 'Desconocido',
            'Curso'    => $e->offering->course->nombre ?? 'Sin curso',
            'Sucursal' => $e->student->branch->nombre ?? 'Sin asignar',
            'Fecha'    => optional($e->fecha)->format('d/m/Y'),
            'Estado'   => ucfirst($e->status),
        ]);
    }

    public function headings(): array
    {
        return ['Alumno', 'Curso', 'Sucursal', 'Fecha de Inscripción', 'Estado'];
    }
}
