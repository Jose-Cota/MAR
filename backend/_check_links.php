<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ros = DB::table('actividad_riesgo')->join('riesgos', 'riesgos.id', '=', 'actividad_riesgo.riesgo_id')->where('riesgos.ejercicio_id', 17)->count();
print_r($ros);
