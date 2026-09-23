<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$projs = DB::table('proyectos')->where('ejercicio_id', 17)->get();
foreach ($projs as $p) {
    $ro = DB::table('responsables_operativos')->where('responsable_operativo_id', $p->responsable_operativo_id)->first();
    echo "Proj {$p->proyecto_id} | RO_ID {$p->responsable_operativo_id} | RO_Ej {$ro->ejercicio_id}\n";
}
