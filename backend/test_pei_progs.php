<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$progs = DB::connection('poa_prod')->table('pei_programas')->get();
foreach ($progs as $p) {
    echo "Prog ID: {$p->pei_programa_id} - Year: {$p->ejercicio_anio}\n";
}
