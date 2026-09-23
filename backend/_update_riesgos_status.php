<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$columns = Schema::getColumnListing('riesgos');
print_r($columns);

if (in_array('status', $columns) || in_array('estatus', $columns)) {
    $col = in_array('status', $columns) ? 'status' : 'estatus';
    
    // Update only for exercise 17 (2026) to be safe
    $affected = DB::table('riesgos')
        ->where('ejercicio_id', 17)
        ->update([$col => 'Captura']);
        
    echo "Updated $affected risks to 'Captura'\n";
} else {
    echo "Status column not found in riesgos table\n";
}
