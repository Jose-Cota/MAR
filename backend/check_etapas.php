<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$etapas = DB::connection('poa_prod')->table('operaciones_ejercicios')
    ->where('ejercicio_id', 2)
    ->get();

foreach ($etapas as $e) {
    echo "Etapa: ID {$e->operacion_ejercicio_id}, Tipo: {$e->tipo}, Creado: {$e->fecha_alta}\n";
}
