<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$urgs = DB::connection('poa_prod')->table('unidades_responsables_gastos')->whereIn('numero', ['01', '02', '11'])->get();
foreach($urgs as $u) {
    echo "URG ID: {$u->unidad_responsable_gasto_id}, Numero: {$u->numero}, Nombre: {$u->nombre}\n";
}

// Fetch ROs for URG 2 (Presidencia)
$ros = DB::connection('poa_prod')->table('responsables_operativos')
    ->join('unidades_responsables_gastos as urg', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->whereIn('responsables_operativos.unidad_responsable_gasto_id', [1, 2])
    ->select('responsables_operativos.responsable_operativo_id', 'urg.numero as urg_numero', 'responsables_operativos.numero', 'responsables_operativos.nombre')
    ->get();
echo "\nROs for URG 1 and 2:\n";
foreach($ros as $ro) {
    echo "ID: {$ro->responsable_operativo_id}, UR: {$ro->urg_numero}, RO: {$ro->numero}, Nombre: {$ro->nombre}\n";
}
