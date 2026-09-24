<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$riesgos = DB::table('0201sadpyrf_mar2026.riesgos')->where('area_id', 2)->get();
echo "Riesgos area_id 2: " . count($riesgos) . "\n";
foreach ($riesgos as $r) {
    echo $r->objetivo . "\n";
}
