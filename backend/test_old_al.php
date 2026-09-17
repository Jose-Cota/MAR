<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sp = DB::connection('poa_prod')->table('subprogramas')->where('numero', '01')->first(); // 2026 subprograma
$oldAlineacionesSp = DB::connection('poa_prod')->table('subprograma_pei_alineaciones')
    ->where('subprograma_id', $sp->subprograma_id)
    ->get();

echo "Old Alineaciones count: " . $oldAlineacionesSp->count() . "\n";
print_r($oldAlineacionesSp);

