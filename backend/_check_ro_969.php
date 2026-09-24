<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$actions = DB::table('acciones_sustantivas')->whereIn('proyecto_id', function($q) { 
    $q->select('proyecto_id')->from('proyectos')->where('responsable_operativo_id', 969); 
})->select('descripcion')->get();
echo json_encode($actions);
