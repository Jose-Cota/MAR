<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$t1 = DB::connection('poa_prod')->select('DESCRIBE metas');
$t2 = DB::connection('poa_prod')->select('DESCRIBE indicadores');
$t3 = DB::connection('poa_prod')->select('DESCRIBE acciones_sustantivas');
$t4 = DB::connection('poa_prod')->select('DESCRIBE actividades_sustantivas');

echo json_encode(['metas'=>$t1, 'indicadores'=>$t2, 'acciones_sustantivas'=>$t3, 'actividades_sustantivas'=>$t4], JSON_PRETTY_PRINT);
