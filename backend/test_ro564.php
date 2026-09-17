<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ros = DB::connection('poa_prod')->table('responsables_operativos as ro')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->select('ro.responsable_operativo_id', 'urg.numero as urg_numero', 'ro.numero', 'ro.nombre')
    ->whereIn('ro.unidad_responsable_gasto_id', [564, 565])
    ->get();

foreach($ros as $ro) {
    echo "ID: {$ro->responsable_operativo_id}, UR: {$ro->urg_numero}, RO: {$ro->numero}, Nombre: {$ro->nombre}\n";
}
