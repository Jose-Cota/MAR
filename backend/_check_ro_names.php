<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::table('proyectos')->where('ejercicio_id', 19)->get();
foreach($proyectos as $p) {
    $ro = DB::table('responsables_operativos')->where('responsable_operativo_id', $p->responsable_operativo_id)->first();
    if ($ro) {
        echo "Proyecto: {$p->proyecto_id} -> RO: {$ro->nombre}\n";
    }
}
