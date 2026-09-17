<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$py = DB::connection('poa_prod')->table('proyectos')->where('nombre', 'like', '%Administración de Recursos Financieros%')->first();
$sp = DB::connection('poa_prod')->table('subprogramas')->where('subprograma_id', $py->subprograma_id)->first();
echo "Proyecto: {$py->nombre}, Subprograma ID: {$py->subprograma_id}, Subprograma num: {$sp->numero}, nombre: {$sp->nombre}\n";
