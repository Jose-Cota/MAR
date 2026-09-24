<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$riesgos = DB::table('0201sadpyrf_mar2026.riesgos')->where('area_id', 17)->get();
foreach ($riesgos as $r) {
    echo "ID: " . $r->id . " | local_id: " . $r->local_id . " | ejercicio: " . $r->ejercicio_id . " | objetivo: " . $r->objetivo . "\n";
}
