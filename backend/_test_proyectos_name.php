<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')->table('proyectos')->where('ejercicio_id', 19)->get();
foreach ($proyectos as $p) {
    if (stripos($p->nombre, 'Jurídica') !== false) {
        echo $p->proyecto_id . " | " . $p->nombre . "\n";
    }
}
