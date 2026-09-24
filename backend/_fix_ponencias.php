<?php
ini_set('memory_limit', '256M');
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$PONENCIAS = [
    'PAAH'  => ['py2027' => [1757, 1758]],
    'PJHR'  => ['py2027' => [1759, 1760]],
    'POVR'  => ['py2027' => [1761, 1762]],
    'PKSL'  => ['py2027' => [1763, 1764]],
    'PLPJC' => ['py2027' => [1765, 1766]],
];

$TEXTOS = [
    1 => "Garantizar la autonomía del Tribunal, respecto del funcionamiento, independencia e imparcialidad de sus decisiones.",
    2 => "Ejercer la función jurisdiccional aplicando la potestad de emitir resoluciones y determinaciones.",
    3 => "Aprobar los criterios de jurisprudencia y tesis relevantes.",
    4 => "Llevar a cabo Sesiones Públicas para la resolución de los medios de impugnación y controversias planteadas ante este Tribunal.",
    5 => "Participar en la integración de las Comisiones de Magistrados/as, que el Pleno determine y presentar los informes y acuerdos correspondientes."
];

$LINKS = [
    1 => 'R5',
    2 => 'R1',
    3 => 'R3',
    4 => 'R2',
    5 => 'R4'
];

DB::beginTransaction();
try {
    foreach ($PONENCIAS as $areaId => $data) {
        $proyIds = $data['py2027'];
        $proyPrincipal = $proyIds[0];

        // 1. Obtener todos los IDs de acciones actuales para borrarlos de la tabla pivote
        $oldActs = DB::table('acciones_sustantivas')->whereIn('proyecto_id', $proyIds)->pluck('accion_sustantiva_id');
        if($oldActs->count() > 0) {
            DB::table('actividad_riesgo')->whereIn('actividad_sustantiva_id', $oldActs)->delete();
        }

        // 2. Borrar las acciones actuales
        DB::table('acciones_sustantivas')->whereIn('proyecto_id', $proyIds)->delete();

        // 3. Obtener los riesgos del area en 2027
        $riesgos = DB::table('riesgos')
            ->where('area_id', function($q) use ($areaId) {
                $q->select('area_id')->from('areas')->where('descripcion', $areaId)->limit(1);
            })
            ->where('ejercicio_id', 19)
            ->get();
        
        $riesgoMap = [];
        foreach($riesgos as $r) {
            $riesgoMap[$r->local_id] = $r->id;
        }

        // 4. Insertar las 5 nuevas acciones y vincularlas
        foreach ($TEXTOS as $num => $txt) {
            $newId = DB::table('acciones_sustantivas')->insertGetId([
                'proyecto_id' => $proyPrincipal,
                'numero' => $num,
                'descripcion' => $txt
            ]);

            $localRisk = $LINKS[$num];
            if (isset($riesgoMap[$localRisk])) {
                DB::table('actividad_riesgo')->insert([
                    'actividad_sustantiva_id' => $newId,
                    'riesgo_id' => $riesgoMap[$localRisk],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
    DB::commit();
    echo "¡Las ponencias han sido corregidas con éxito!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
