<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$riesgos = DB::table('0201sadpyrf_mar2026.riesgos')->where('objetivo', 'LIKE', '%transversalización%')->get();
foreach ($riesgos as $r) {
    echo "Risk ID: " . $r->id . " | area_id: " . $r->area_id . " | objetivo: " . $r->objetivo . "\n";
}
