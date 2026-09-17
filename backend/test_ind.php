<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$indicadores = DB::connection('poa_prod')->table('indicadores')->whereIn('meta_id', [7068, 7070, 7072, 7074])->get();
print_r($indicadores);
