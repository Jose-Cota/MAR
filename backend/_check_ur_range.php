<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
$max = DB::table('unidades_responsables_gastos')->max('unidad_responsable_gasto_id');
$min = DB::table('unidades_responsables_gastos')->min('unidad_responsable_gasto_id');
echo "Min UR ID: $min, Max UR ID: $max\n";
