<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$urgs = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', '<', 240)->get();
foreach ($urgs as $u) {
    if (stripos($u->nombre, 'Presidencia') !== false) {
        echo $u->unidad_responsable_gasto_id . " | " . $u->numero . " | " . $u->nombre . "\n";
    }
}
