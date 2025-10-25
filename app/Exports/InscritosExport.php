<?php

namespace App\Exports;

use App\Models\Enrollment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InscritosExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
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
            'Fecha de Inscripción' => optional($e->fecha)->format('d/m/Y'),
            'Estado'   => ucfirst($e->status ?? 'Sin estado'),
        ]);
    }

    public function headings(): array
    {
        return ['Alumno', 'Curso', 'Sucursal', 'Fecha de Inscripción', 'Estado'];
    }

    public function styles(Worksheet $sheet)
    {
        // === 1️⃣ Encabezados destacados ===
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'E0E6F8'], // Azul suave
            ],
        ]);

        // === 2️⃣ Ajuste de columnas (auto-size + margen extra) ===
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $currentWidth = $sheet->getColumnDimension($col)->getWidth();
            $sheet->getColumnDimension($col)->setWidth($currentWidth + 4);
        }

        // === 3️⃣ Centramos columnas de fecha y estado ===
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("D2:E{$lastRow}")
            ->getAlignment()
            ->setHorizontal('center');

        // === 4️⃣ Opcional: bordes suaves (presentación elegante) ===
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $lastRow)
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => 'thin',
                        'color' => ['rgb' => 'D0D0D0'],
                    ],
                ],
            ]);

        return [];
    }
}
