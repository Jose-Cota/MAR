<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$alternos = DB::connection('mar_alterna')->table('riesgo_indicadores')->get()->keyBy('id');
$actuales = DB::table('riesgo_indicadores')->get()->keyBy('id');

$modificados = 0;
foreach($actuales as $id => $act) {
    if (isset($alternos[$id])) {
        if ($alternos[$id]->nombre !== $act->nombre || $alternos[$id]->formula !== $act->formula) {
            $modificados++;
            echo "ID $id cambiado:\n";
            echo "  Antes: " . $alternos[$id]->nombre . " | " . $alternos[$id]->formula . "\n";
            echo "  Ahora: " . $act->nombre . " | " . $act->formula . "\n\n";
        }
    }
}
echo "Total modificados: $modificados\n";
