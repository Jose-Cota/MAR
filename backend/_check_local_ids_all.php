<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$risks27 = DB::table('riesgos')->where('ejercicio_id', 19)->select('local_id', 'area_id')->get();
foreach ($risks27 as $r) {
    echo "{$r->area_id} - {$r->local_id}\n";
}
