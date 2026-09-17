<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TablaProyectosExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents
{
    protected $proyectos;

    public function __construct($proyectos)
    {
        $this->proyectos = $proyectos;
    }

    public function collection()
    {
        return $this->proyectos;
    }

    public function headings(): array
    {
        return [
            ['Tabla de Proyectos 2027'],
            ['Proyecto', 'Actividad sustantiva', 'Recursos asociados']
        ];
    }

    public function map($proyecto): array
    {
        return [
            $proyecto->py_numero . ' ' . $proyecto->py_nombre,
            $proyecto->actividad_sustantiva,
            $proyecto->recursos_asociados,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 50,
            'C' => 40,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:C1');
        
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:C' . $highestRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:C' . $highestRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']], 'font' => ['color' => ['argb' => 'FFFFFFFF']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setRight(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
            },
        ];
    }
}
