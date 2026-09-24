<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$riesgos = DB::table('0201sadpyrf_mar2026.riesgos')->where('objetivo', 'LIKE', '%Sustanciar%')->get();
foreach ($riesgos as $r) {
    echo "ID: " . $r->id . " | local_id: " . $r->local_id . " | area_id: " . $r->area_id . " | ejercicio: " . $r->ejercicio_id . " | objetivo: " . $r->objetivo . "\n";
}
