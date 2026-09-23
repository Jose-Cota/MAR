<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$risks = DB::table('proyectos')->where('ejercicio_id', 19)->where('nombre', 'like', '%Presidencia%')->value('responsable_operativo_id');
echo \;
    echo "Risk local_id: {$r->local_id}, riesgo: {$r->riesgo}\n";
}
