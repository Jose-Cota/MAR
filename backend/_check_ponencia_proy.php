<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$proys = DB::connection('poa_prod')->table('proyectos')->where('ejercicio_id', 19)->where('nombre', 'like', '%Ponencia%')->get();
foreach ($proys as $p) {
    echo "Proyecto: " . $p->nombre . " | RO ID: " . $p->responsable_operativo_id . "\n";
}
