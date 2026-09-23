<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rs = DB::table('riesgos')->where('ejercicio_id', 19)->get(['id', 'factores', 'factores_internos']);
foreach($rs as $r) {
    if($r->factores_internos) {
        echo $r->id . ' has internos: ' . $r->factores_internos . "\n";
    }
}
