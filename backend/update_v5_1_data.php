<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Riesgo;
use App\Models\RiesgoControl;
use App\Models\RiesgoIndicador;

$obsolete = [
    'PRES-2026-R4','SG-2026-R5','SA-2026-R5','DRH-2026-R5','DRMySG-2026-R6','CI-2026-R6',
    'DGJ-2026-R5','CCSyRP-2026-R4','CTyDP-2026-R6','IFyC-2026-R3','CCLA-2026-R4','USI-2026-R6',
    'UEyJ-2026-R4','CDyP-2026-R5','CA-2026-R5','CDHyG-2026-R4','DPPCyPD-2026-R4','CVyRI-2026-R4',
    'UEPS-2026-R4','PAAH-2026-R6','PJHR-2026-R6','POVR-2026-R6','PKSL-2026-R6','PLPJC-2026-R6'
];

echo "Borrando riesgos obsoletos...\n";
foreach ($obsolete as $local_id) {
    // local_id in DB is usually 'local_id' but could be formatted. Let's check matching logic.
    $r = Riesgo::where('local_id', $local_id)->first();
    if ($r) {
        // Riesgo models have cascade delete set up usually, but let's do it manually.
        RiesgoControl::where('riesgo_id', $r->id)->delete();
        RiesgoIndicador::where('riesgo_id', $r->id)->delete();
        DB::table('actividad_riesgo')->where('riesgo_id', $r->id)->delete();
        $r->delete();
        echo "Deleted: {$local_id}\n";
    }
}

echo "\nProcesando SEED...\n";
$jsonPath = __DIR__.'/../scratch_seed.json';
if (!file_exists($jsonPath)) {
    die("Error: no se encontró scratch_seed.json\n");
}
$seed = json_decode(file_get_contents($jsonPath), true);

$risksCount = 0;
foreach ($seed['risks'] as $r) {
    if (in_array($r['id'], $obsolete)) continue;
    
    // Find or create risk
    $risk = Riesgo::firstOrNew(['local_id' => $r['id']]);
    
    $risk->area_id = $r['areaId'];
    $risk->ejercicio_id = $r['exercise'];
    $risk->objetivo = $r['objective'] ?? null;
    $risk->riesgo = $r['risk'] ?? null;
    $risk->probabilidad = $r['probability'] ?? null;
    $risk->impacto = $r['impact'] ?? null;
    
    if (isset($r['factors'])) {
        $risk->factores_internos = $r['factors']['internal'] ?? null;
        $risk->factores_externos = $r['factors']['external'] ?? null;
    }
    
    $risk->save();
    $risksCount++;
    
    // Controles
    if (isset($r['controls'])) {
        RiesgoControl::where('riesgo_id', $risk->id)->delete();
        foreach ($r['controls'] as $c) {
            $control = new RiesgoControl();
            $control->riesgo_id = $risk->id;
            $control->texto = $c['description'] ?? '';
            $control->save();
        }
    }
    
    // Indicadores
    if (isset($r['indicators'])) {
        RiesgoIndicador::where('riesgo_id', $risk->id)->delete();
        foreach ($r['indicators'] as $i) {
            $indicador = new RiesgoIndicador();
            $indicador->riesgo_id = $risk->id;
            $indicador->nombre = $i['name'] ?? '';
            $indicador->periodicidad = $i['periodicity'] ?? '';
            $indicador->formula = $i['formula'] ?? null;
            $indicador->numerador = $i['numeratorLabel'] ?? null;
            $indicador->denominador = $i['denominatorLabel'] ?? null;
            $indicador->tipo = $i['type'] ?? null;
            $indicador->unidad = $i['unit'] ?? null;
            $indicador->sentido = $i['direction'] ?? null;
            $indicador->save();
        }
    }
}

echo "Actualizados $risksCount riesgos exitosamente.\n";
