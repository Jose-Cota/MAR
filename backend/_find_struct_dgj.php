<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$urgs = DB::table('unidades_responsables_gastos')
    ->where('ejercicio_id', 19)
    ->where('unidad_responsable_gasto_id', '>=', 240)
    ->get();
foreach($urgs as $u) {
    if(strpos($u->nombre, 'Jurídica') !== false) {
        echo "Estructural DGJ es ID: {$u->unidad_responsable_gasto_id} - {$u->nombre}\n";
    }
}
