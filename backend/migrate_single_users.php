<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$users = DB::connection('poa_prod')->table('usuarios_poa')->whereNotNull('area_id')->where('area_id', '>', 0)->get();

$count = 0;
foreach($users as $u) {
    DB::connection('poa_prod')->table('usuario_unidad_responsable')->updateOrInsert([
        'usuario_poa_id' => $u->usuario_poa_id,
        'unidad_responsable_gasto_id' => $u->area_id
    ]);
    $count++;
}

echo "Migrated legacy area_ids to pivot table for {$count} users.\n";
