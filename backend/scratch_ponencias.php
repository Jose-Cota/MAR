<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$urgs = DB::table('urgs')->where('nombre', 'LIKE', '%Ponencia%')->get();

foreach ($urgs as $urg) {
    echo "PONENCIA: " . $urg->clave . " - " . $urg->nombre . "\n";
    $riesgos = DB::table('riesgos')
        ->where('urg_id', $urg->id)
        ->where('ejercicio_id', 1) // 1 might be 2026? let's check year
        ->get();
    
    if ($riesgos->isEmpty()) {
        // try to find by another column if ejercicio_id is not 1
        $riesgos = DB::table('riesgos')->where('urg_id', $urg->id)->get();
    }
    
    foreach ($riesgos as $r) {
        echo "  - Riesgo: " . ($r->descripcion ?? $r->nombre ?? $r->riesgo ?? 'ID: '.$r->id) . "\n";
    }
}
