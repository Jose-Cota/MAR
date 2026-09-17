<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$estatus = DB::connection('poa_prod')->table('proyectos')->select('status')->distinct()->get();
foreach ($estatus as $e) {
    echo "Status: '" . $e->status . "' | Length: " . strlen($e->status) . " | Hex: " . bin2hex($e->status) . "\n";
}
