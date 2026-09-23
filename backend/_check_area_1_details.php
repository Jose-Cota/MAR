<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$risks = DB::table('riesgos')->where('ejercicio_id', 19)->where('area_id', 1)->get();
foreach ($risks as $r) {
    echo "Risk local_id: {$r->local_id}, riesgo: {$r->riesgo}\n";
}
