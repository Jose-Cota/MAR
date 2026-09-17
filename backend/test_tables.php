<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::connection('poa_prod')->select('SHOW TABLES');
$tableNames = [];
foreach($tables as $t) {
    $tableNames[] = array_values((array)$t)[0];
}
echo "Tables: \n" . implode("\n", $tableNames) . "\n";
