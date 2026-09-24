<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$unidades = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', '<', 240)->get();
$areas = DB::table('0201sadpyrf_mar2026.areas')->get();

foreach ($unidades as $u) {
    if (stripos($u->nombre, 'Controversias') !== false || stripos($u->nombre, 'Derechos') !== false) {
        $matchedArea = $areas->first(function($a) use ($u) {
            if (trim($a->nombre) === trim($u->nombre)) return true;
            if (stripos($a->nombre, $u->nombre) !== false) return true;
            if (stripos($u->nombre, $a->nombre) !== false) return true;
            return false;
        });
        echo "URG: " . $u->nombre . " -> MAR: " . ($matchedArea ? $matchedArea->nombre : 'NULL') . " (ID: " . ($matchedArea ? $matchedArea->area_id : 'NULL') . ")\n";
    }
}
