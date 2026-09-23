<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Fix Secretaría Administrativa
DB::table('proyectos')
    ->where('ejercicio_id', 17)
    ->where('nombre', 'POA 2026 – Secretaría Administrativa')
    ->update(['responsable_operativo_id' => 446]);

echo "Updated Secretaría Administrativa to RO 446\n";

// Let's quickly verify if any other projects got assigned to wrong ROs
// By checking what RO each project got and printing the name of the project vs name of RO
$projs = DB::table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->where('proyectos.ejercicio_id', 17)
    ->select('proyectos.proyecto_id', 'proyectos.nombre as p_nombre', 'responsables_operativos.responsable_operativo_id as ro_id', 'responsables_operativos.nombre as ro_nombre')
    ->get();

foreach ($projs as $p) {
    echo "{$p->p_nombre} ===> RO {$p->ro_id}: {$p->ro_nombre}\n";
}
