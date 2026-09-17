<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AvanceMensualExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $proyecto;

    public function __construct($proyecto)
    {
        $this->proyecto = $proyecto;
    }

    public function view(): View
    {
        return view('exports.avance_mensual', [
            'proyecto' => $this->proyecto
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
        ];
    }
}
