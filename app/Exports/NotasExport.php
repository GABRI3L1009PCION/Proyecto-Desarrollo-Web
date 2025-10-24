<?php

namespace App\Exports;

use App\Models\Grade;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class NotasExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $inicio;
    protected $fin;
    protected $cursoId;
    protected $grado;

    public function __construct($inicio = null, $fin = null, $cursoId = null, $grado = null)
    {
        $this->inicio = $inicio;
        $this->fin = $fin;
        $this->cursoId = $cursoId;
        $this->grado = $grado;
    }

    public function collection()
    {
        return Grade::with(['enrollment.offering.course', 'enrollment.student'])
            ->when($this->cursoId, fn($q) =>
            $q->whereHas('enrollment.offering.course', fn($qc) =>
            $qc->where('id', $this->cursoId)
            )
            )
            ->when($this->grado, fn($q) =>
            $q->whereHas('enrollment.student', fn($qs) =>
            $qs->where('grade', $this->grado)
            )
            )
            ->when($this->inicio, fn($q) =>
            $q->whereDate('grades.updated_at', '>=', $this->inicio)
            )
            ->when($this->fin, fn($q) =>
            $q->whereDate('grades.updated_at', '<=', $this->fin)
            )
            ->orderBy('grades.updated_at', 'desc')
            ->get()
            ->map(fn($g) => [
                'Curso'   => $g->enrollment->offering->course->nombre ?? 'Sin curso',
                'Grado'   => $g->enrollment->student->grade ?? 'N/A',
                'Nivel'   => $g->enrollment->student->level ?? 'N/A',
                'Alumno'  => $g->enrollment->student->nombres ?? 'Desconocido',
                'P1'      => $g->parcial1 ?? '-',
                'P2'      => $g->parcial2 ?? '-',
                'Final'   => $g->final ?? '-',
                'Total'   => $g->total ?? '-',
                'Estado'  => $g->estado ?? '-',
                'Fecha'   => optional($g->updated_at)->format('d/m/Y H:i'),
            ]);
    }

    public function headings(): array
    {
        return ['Curso', 'Grado', 'Nivel', 'Alumno', 'P1', 'P2', 'Final', 'Total', 'Estado', 'Fecha'];
    }
}
