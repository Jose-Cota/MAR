<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$arr = DB::table('unidades_responsables_gastos')->where('nombre', 'like', '%Planeación%')->get()->toArray();
print_r($arr);
