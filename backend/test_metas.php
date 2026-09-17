<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$proy = DB::connection('poa_prod')->table('metas')->where('proyecto_id', 1402)->get();
echo json_encode($proy);
