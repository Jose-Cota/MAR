<?php
$keys = [
    ['01','01','01','01','02'],
    ['01','02','01','01','02'],
    ['01','03','01','01','02'],
    ['01','04','01','01','02'],
    ['01','05','01','01','02']
];

$results = [];

foreach($keys as $k) {
    $p = DB::connection('poa_prod')->table('proyectos as py')
        ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
        ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
        ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
        ->join('ejercicios as ej', 'pg.ejercicio_id', '=', 'ej.ejercicio_id')
        ->where('urg.numero', $k[0])
        ->where('ro.numero', $k[1])
        ->where('pg.numero', $k[2])
        ->where('sp.numero', $k[3])
        ->where('py.numero', $k[4])
        ->orderByDesc('ej.ejercicio')
        ->select(
            'py.proyecto_id', 
            'py.nombre', 
            'py.objetivo', 
            'py.justificacion', 
            'py.descripcion', 
            'py.responsable_ficha',
            'py.puesto_responsable_ficha',
            'py.autorizante_nombre',
            'py.autorizante_puesto',
            'ej.ejercicio'
        )
        ->first();

    if ($p) {
        $act = DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
        if ($act->isEmpty()) {
            $act = DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
        }
        
        $metas = DB::connection('poa_prod')->table('metas')->where('proyecto_id', $p->proyecto_id)->get();
        
        $results['RO_'.$k[1]] = [
            'proyecto' => $p,
            'actividades' => count($act) . " actividades",
            'metas' => count($metas) . " metas"
        ];
    } else {
        $results['RO_'.$k[1]] = 'Not found';
    }
}

file_put_contents('fichas_output.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Done\n";
