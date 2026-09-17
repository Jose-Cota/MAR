<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
try {
    $res = DB::connection('poa_prod')->select("SHOW COLUMNS FROM programas");
    print_r($res);
} catch (Exception $e) {}
