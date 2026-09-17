<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sp12 = DB::connection('poa_prod')->table('subprogramas')->where('numero', '12')->first();
$align = DB::connection('poa_prod')->table('subprograma_pei_alineaciones')->where('subprograma_id', $sp12->subprograma_id)->get();
echo "Alignments for SP 12: " . count($align) . "\n";
