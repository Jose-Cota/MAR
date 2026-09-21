<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rs = App\Models\Riesgo::with(['indicadores'])->where('ejercicio_id', 17)->where('area_id', 2)->get();
echo 'Total Risks (Area 2, Ejercicio 17): ' . count($rs) . PHP_EOL;

$withInd = 0;
foreach($rs as $r) {
    if(count($r->indicadores) > 0) {
        $withInd++;
        $ind = $r->indicadores[0];
        echo "Risk {$r->id} has indicator: " . ($ind->formula ?? 'No formula') . ' | ' . ($ind->numerador ?? 'No num') . ' | ' . ($ind->denominador ?? 'No den') . ' | ' . ($ind->periodicidad ?? 'No per') . PHP_EOL;
    } else {
        echo "Risk {$r->id} has NO indicators." . PHP_EOL;
    }
}
echo 'With Indicadores: ' . $withInd . PHP_EOL;
