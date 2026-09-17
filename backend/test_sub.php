<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
try {
    $res = DB::connection('poa_prod')->select("SELECT COUNT(*) as c FROM proyectos WHERE subprograma_id IS NULL OR subprograma_id = 0");
    print_r($res);
} catch (Exception $e) {}
