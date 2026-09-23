<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$res = DB::table('acciones_sustantivas')->where('descripcion', 'LIKE', '%Informes trimestrales, anuales y requeridos%')->get();
foreach ($res as $r) {
    echo $r->id . ' | ' . $r->descripcion . "\n";
}
