<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $pys = DB::connection('poa_prod')->select("SELECT numero, nombre FROM proyectos LIMIT 10");
    foreach($pys as $p) {
        echo "PY: {$p->numero}\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
