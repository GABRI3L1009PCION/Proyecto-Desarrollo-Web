<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SucursalExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $branch_id;

    public function __construct($branch_id = null)
    {
        $this->branch_id = $branch_id;
    }

    public function collection()
    {
        // === Caso 1: sucursal específica (detalle de alumnos) ===
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

        // === Caso 2: resumen general de sucursales ===
        return Student::join('branches', 'students.branch_id', '=', 'branches.id')
            ->select('branches.nombre as Sucursal', DB::raw('COUNT(students.id) as Total_Alumnos'))
            ->groupBy('branches.nombre')
            ->orderBy('branches.nombre')
            ->get()
            ->map(fn($r) => [
                'Sucursal' => $r->Sucursal,
                'Total de Alumnos' => $r->Total_Alumnos,
            ]);
    }

    public function headings(): array
    {
        return $this->branch_id
            ? ['Alumno', 'Grado', 'Nivel', 'Sucursal']
            : ['Sucursal', 'Total de Alumnos'];
    }

    public function styles(Worksheet $sheet)
    {
        // === 1️⃣ Estilo encabezado ===
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'E0E6F8'], // Azul suave consistente
            ],
        ]);

        // === 2️⃣ Ajuste de ancho (ShouldAutoSize + margen adicional) ===
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $currentWidth = $sheet->getColumnDimension($col)->getWidth();
            $sheet->getColumnDimension($col)->setWidth($currentWidth + 4);
        }

        // === 3️⃣ Centramos columnas numéricas y sucursales ===
        $lastRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // Si el export tiene columna de totales, centrarla
        if (!$this->branch_id) {
            $sheet->getStyle("B2:B{$lastRow}")
                ->getAlignment()
                ->setHorizontal('center');
        } else {
            $sheet->getStyle("B2:C{$lastRow}")
                ->getAlignment()
                ->setHorizontal('center');
        }

        // === 4️⃣ Bordes suaves para toda la tabla ===
        $sheet->getStyle("A1:{$highestCol}{$lastRow}")
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
