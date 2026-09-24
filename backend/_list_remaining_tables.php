<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$dbName = 'Tables_in_' . env('DB_DATABASE');

$allTables = [];
foreach($tables as $t) {
    $tableName = $t->{$dbName} ?? array_values((array)$t)[0];
    $allTables[] = $tableName;
}

echo implode("\n", $allTables);
