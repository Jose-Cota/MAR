<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::table('proyectos')->where('ejercicio_id', 17)->get();
foreach ($proyectos as $p) {
    if (strpos(mb_strtolower($p->nombre), 'presidencia') !== false) {
        echo "Found: {$p->nombre} (RO: {$p->responsable_operativo_id})\n";
    }
}
