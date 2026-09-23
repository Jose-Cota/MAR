<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::table('riesgos')->where('ejercicio_id', 17)->where('local_id', 'R1')->get();
print_r(\);
    if (strpos(mb_strtolower($p->nombre), 'presidencia') !== false) {
        echo "Found: {$p->nombre} (RO: {$p->responsable_operativo_id})\n";
    }
}
