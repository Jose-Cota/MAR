<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$schema = DB::connection('poa_prod')->getSchemaBuilder()->getColumnListing('subprogramas');
echo "subprogramas columns: " . implode(', ', $schema) . "\n";

// Let's also check if there is an old table for pei alignment?
$tables = DB::connection('poa_prod')->select('SHOW TABLES LIKE "%pei%"');
foreach($tables as $t) {
    $name = array_values((array)$t)[0];
    echo "PEI Table: $name\n";
}

$sp = DB::table('subprogramas')->where('subprograma_id', 295)->first();
echo "Subprograma 295: " . json_encode($sp) . "\n";

