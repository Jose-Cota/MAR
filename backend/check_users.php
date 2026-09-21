<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Check usuarios_poa table
$users = DB::table('usuarios_poa')->take(8)->get();
echo "usuarios_poa:\n";
foreach ($users as $u) {
    echo "  id={$u->id}, usuario={$u->usuario}, area_id=" . ($u->area_id ?? 'NULL') . ", nivel={$u->nivel}\n";
    $urgIds = DB::table('usuario_unidad_responsable')
        ->where('usuario_poa_id', $u->id)
        ->where('unidad_responsable_gasto_id', '>', 0)
        ->pluck('unidad_responsable_gasto_id');
    echo "    UURs > 0: " . $urgIds->join(', ') . "\n";
}

// Check what URG id=20 is in main table
$urg20 = DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', 20)->first();
echo "\nURG id=20: " . json_encode($urg20) . "\n";
