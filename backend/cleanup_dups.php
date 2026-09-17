<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Cleanup usuarios_responsables_operativos
$ros = DB::connection('poa_prod')->table('usuarios_responsables_operativos')->get();
$seen = [];
$deleteIds = [];
foreach($ros as $row) {
    $key = $row->usuario_poa_id . '_' . $row->responsable_operativo_id;
    if(isset($seen[$key])) {
        $deleteIds[] = $row->usuario_responsable_operativo_id;
    } else {
        $seen[$key] = true;
    }
}

if(count($deleteIds) > 0) {
    DB::connection('poa_prod')->table('usuarios_responsables_operativos')
        ->whereIn('usuario_responsable_operativo_id', $deleteIds)
        ->delete();
    echo "Removed " . count($deleteIds) . " duplicate RO assignments.\n";
} else {
    echo "No duplicate RO assignments found.\n";
}

// Cleanup usuario_unidad_responsable
$urs = DB::connection('poa_prod')->table('usuario_unidad_responsable')->get();
$seenUr = [];
$deleteUrIds = [];
foreach($urs as $row) {
    $key = $row->usuario_poa_id . '_' . $row->unidad_responsable_gasto_id;
    if(isset($seenUr[$key])) {
        $deleteUrIds[] = $row->id;
    } else {
        $seenUr[$key] = true;
    }
}

if(count($deleteUrIds) > 0) {
    DB::connection('poa_prod')->table('usuario_unidad_responsable')
        ->whereIn('id', $deleteUrIds)
        ->delete();
    echo "Removed " . count($deleteUrIds) . " duplicate UR assignments.\n";
} else {
    echo "No duplicate UR assignments found.\n";
}
