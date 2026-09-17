<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class AvanceTrimestralExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $ejercicio;
    protected $trimestre;

    public function __construct($ejercicio, $trimestre = null)
    {
        $this->ejercicio = $ejercicio;
        $this->trimestre = $trimestre; // Para futuro filtro
    }

    public function view(): View
    {
        // Obtener datos consolidados usando la misma logica de TableroAdministrativo u obtener un mockup 
        // ya que la BD real no esta enlazada en este momento.
        // Pero intentamos obtener de `poa_prod` si existe.
        
        $datos = [];
        try {
            // Unidades Responsables (simplificado)
            $unidades = DB::connection('poa_prod')->table('unidades_responsables_gasto')
                ->where('ejercicio_id', $this->ejercicio)
                ->get();
            $datos = $unidades;
        } catch (\Exception $e) {
            // Fallback si no hay BD, enviamos vacio o mock
            $datos = [];
        }

        return view('exports.avance_trimestral', [
            'ejercicio' => $this->ejercicio,
            'trimestre' => $this->trimestre,
            'datos' => $datos,
            'meses' => ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilizar la primera fila
            1    => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            // Estilizar cabeceras
            2    => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF800000'] // Color guinda
                ]
            ],
        ];
    }
}
