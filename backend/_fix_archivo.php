<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Coordinación de Archivo was the only one that failed and fell back to 438 (Ponencia 1)
// Let's find its project(s) and update it.
// Wait, I can just update the project where nombre contains "archivo" or where it's currently 438 but shouldn't be.
// Let's check which ones fell back to 438.
$p438 = DB::table('proyectos')->where('ejercicio_id', 17)->where('responsable_operativo_id', 438)->get();
foreach ($p438 as $p) {
    echo "ID: {$p->proyecto_id} | Name: {$p->nombre}\n";
    if (strpos(mb_strtolower($p->nombre), 'archivo') !== false) {
        DB::table('proyectos')->where('proyecto_id', $p->proyecto_id)->update(['responsable_operativo_id' => 463]);
        echo "UPDATED TO 463!\n";
    }
}
