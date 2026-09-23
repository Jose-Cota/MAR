<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$arr = DB::table('responsables_operativos')->whereIn('responsable_operativo_id', [446, 447, 448, 449, 459])->pluck('unidad_responsable_gasto_id', 'responsable_operativo_id')->toArray();
print_r($arr);
