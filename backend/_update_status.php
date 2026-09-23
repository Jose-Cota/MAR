<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $affected = DB::table('riesgos')
        ->where('ejercicio_id', 19)
        ->where('status', 'Borrador')
        ->update(['status' => 'Captura']);
        
    echo "SUCCESS: Updated $affected risks from Borrador to Captura for 2027.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
