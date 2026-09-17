<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sps = DB::connection('poa_prod')->table('subprogramas')->get();
echo "Total subprogramas: " . count($sps) . "\n";

$mapped = DB::connection('poa_prod')->table('subprograma_pei_alineaciones')->get();
echo "Total mapped subprogramas: " . count($mapped) . "\n";

