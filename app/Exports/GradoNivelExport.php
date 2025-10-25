<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradoNivelExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $tipo;

    public function __construct($tipo = 'grado_nivel')
    {
        $this->tipo = $tipo;
    }

    public function collection()
    {
        // === Selección según tipo recibido desde el Blade ===
        if ($this->tipo === 'grado') {
            return Student::select('grade as Grado', DB::raw('COUNT(*) as Total'))
                ->groupBy('grade')
                ->orderBy('grade')
                ->get();
        }

        if ($this->tipo === 'nivel') {
            return Student::select('level as Nivel', DB::raw('COUNT(*) as Total'))
                ->groupBy('level')
                ->orderBy('level')
                ->get();
        }

        // Por defecto: grado + nivel
        return Student::select('grade as Grado', 'level as Nivel', DB::raw('COUNT(*) as Total'))
            ->groupBy('grade', 'level')
            ->orderBy('grade')
            ->orderBy('level')
            ->get();
    }

    public function headings(): array
    {
        return match ($this->tipo) {
            'grado' => ['Grado', 'Total de Alumnos'],
            'nivel' => ['Nivel', 'Total de Alumnos'],
            default => ['Grado', 'Nivel', 'Total de Alumnos'],
        };
    }

    public function styles(Worksheet $sheet)
    {
        // === 1️⃣ Estilo de encabezados ===
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'E0E6F8'], // Azul claro
            ],
        ]);

        // === 2️⃣ Agrega margen de ancho extra ===
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $currentWidth = $sheet->getColumnDimension($col)->getWidth();
            $sheet->getColumnDimension($col)->setWidth($currentWidth + 3);
        }

        // === 3️⃣ Centra valores numéricos ===
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A2:" . $sheet->getHighestColumn() . $lastRow)
            ->getAlignment()
            ->setHorizontal('center');

        return [];
    }
}
