<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class AperturaProgramaticaExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $ejercicio;

    public function __construct($ejercicio)
    {
        $this->ejercicio = $ejercicio;
    }

    public function view(): View
    {
        $datos = [];
        try {
            // Este es un volcado básico para la maqueta, adaptado de lo que pedía la Apertura.
            // Cuando la VPN esté lista, conectaremos con las uniones a "metas", "proyectos", "programas", "subprogramas".
            
            // Mock array para mantener la funcionalidad y poder descargar
            $datos = [
                (object)[
                    'urg' => '11', 'ro' => '01', 'pg' => '112', 'sp' => '22', 'py' => '01',
                    'denominacion' => 'Meta de prueba sin DB',
                    'unidad_medida' => 'Reporte',
                    'meses' => [10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10],
                    'total' => 120
                ]
            ];
            
            // Intentaremos obtener datos reales de poa_prod
            $metas = DB::connection('poa_prod')->table('metas as m')
                ->join('proyectos as p', 'm.proyecto_id', '=', 'p.proyecto_id')
                ->join('unidades_medidas as um', 'm.unidad_medida_id', '=', 'um.unidad_medida_id')
                ->join('subprogramas as sp', 'p.subprograma_id', '=', 'sp.subprograma_id')
                ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
                ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
                ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
                ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
                ->where('ej.ejercicio', $this->ejercicio)
                ->where('m.tipo', 'principal')
                ->select(
                    'urg.numero as urg',
                    'ro.numero as ro',
                    'pg.numero as pg',
                    'sp.numero as sp',
                    'p.numero as py',
                    'm.nombre as denominacion',
                    'um.nombre as unidad_medida',
                    'm.meta_id'
                )
                ->get();

            if ($metas->count() > 0) {
                $datos = [];
                foreach ($metas as $meta) {
                    $programadas = DB::connection('poa_prod')->table('meses_metas_programadas')
                        ->where('meta_id', $meta->meta_id)
                        ->orderBy('mes_id')
                        ->pluck('numero', 'mes_id')
                        ->toArray();
                        
                    $mesesArr = [];
                    $total = 0;
                    for ($i = 1; $i <= 12; $i++) {
                        $val = $programadas[$i] ?? 0;
                        $mesesArr[] = $val;
                        $total += $val;
                    }

                    $datos[] = (object)[
                        'urg' => $meta->urg,
                        'ro'  => $meta->ro,
                        'pg'  => $meta->pg,
                        'sp'  => $meta->sp,
                        'py'  => $meta->py,
                        'denominacion' => $meta->denominacion,
                        'unidad_medida' => $meta->unidad_medida,
                        'meses' => $mesesArr,
                        'total' => $total
                    ];
                }
            }

        } catch (\Exception $e) {
            // Se silencia y manda el array Mock
        }

        return view('exports.apertura_programatica', [
            'ejercicio' => $this->ejercicio,
            'datos' => $datos,
            'meses' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            2    => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            3    => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF244062'] // Color azul oscuro/guinda
                ]
            ],
            4    => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF000000'] // Color negro para meses
                ]
            ],
        ];
    }
}
