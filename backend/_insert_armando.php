<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

DB::connection('poa_prod')->beginTransaction();

try {
    echo "Clonando actividades para Ponencia del Magistrado Armando Ambriz Hernández...\n";
    
    // El nombre del RO
    $ro_nombre = "Ponencia del Magistrado Armando Ambriz Hernández";
    
    // Obtener proyecto
    $p = DB::connection('poa_prod')->table('proyectos as p')
        ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
        ->where('p.ejercicio_id', 19)
        ->where('ro.nombre', $ro_nombre)
        ->where('p.nombre', 'like', '%Ponencia realiza%')
        ->select('p.proyecto_id', 'urg.unidad_responsable_gasto_id')
        ->first();
        
    if (!$p) {
        die("Proyecto no encontrado para Armando Ambriz\n");
    }

    $actividades_std = [
        [
            "no_consecutivo" => 1,
            "actividad_alineacion" => "Garantizar la autonomía del Tribunal, respecto del funcionamiento, independencia e imparcialidad de sus decisiones.",
            "riesgos_vinculados" => "R5"
        ],
        [
            "no_consecutivo" => 2,
            "actividad_alineacion" => "Ejercer la función jurisdiccional aplicando la potestad de emitir resoluciones y determinaciones.",
            "riesgos_vinculados" => "R1"
        ],
        [
            "no_consecutivo" => 3,
            "actividad_alineacion" => "Aprobar los criterios de jurisprudencia y tesis relevantes.",
            "riesgos_vinculados" => "R3"
        ],
        [
            "no_consecutivo" => 4,
            "actividad_alineacion" => "Llevar a cabo Sesiones Públicas para la resolución de los medios de impugnación y controversias planteadas ante este Tribunal.",
            "riesgos_vinculados" => "R2"
        ],
        [
            "no_consecutivo" => 5,
            "actividad_alineacion" => "Participar en la integración de las Comisiones de Magistrados/as, que el Pleno determine y presentar los informes y acuerdos correspondientes.",
            "riesgos_vinculados" => "R4"
        ]
    ];
    
    // Limpiar previas por si acaso
    $act_ids = DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $p->proyecto_id)->pluck('id');
    if ($act_ids->count() > 0) {
        DB::connection('poa_prod')->table('actividad_riesgo')->whereIn('actividad_sustantiva_id', $act_ids)->delete();
        DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $p->proyecto_id)->delete();
    }
    
    $inserted = 0;
    foreach ($actividades_std as $item) {
        $act_id = DB::connection('poa_prod')->table('actividades_sustantivas')->insertGetId([
            'proyecto_id' => $p->proyecto_id,
            'numero' => $item['no_consecutivo'],
            'descripcion' => $item['actividad_alineacion'],
            'recursos_asociados' => '',
            'es_resumida' => 1
        ]);
        $inserted++;
        
        $r_local = $item['riesgos_vinculados'];
        
        // Riesgo para Armando Ambriz (Area catalog ID 21 o entre 21-25)
        $riesgo = DB::connection('poa_prod')->table('riesgos')
            ->where('ejercicio_id', 19)
            ->where('local_id', $r_local)
            ->whereBetween('area_id', [21, 25])
            ->first();
            
        if ($riesgo) {
            DB::connection('poa_prod')->table('actividad_riesgo')->insert([
                'actividad_sustantiva_id' => $act_id,
                'riesgo_id' => $riesgo->id
            ]);
        } else {
            echo "   [WARNING] Riesgo $r_local no encontrado para Armando\n";
        }
    }

    DB::connection('poa_prod')->commit();
    echo "PROCESO COMPLETADO EXITOSAMENTE. Insertadas $inserted actividades para Armando.\n";
} catch (\Exception $e) {
    DB::connection('poa_prod')->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
