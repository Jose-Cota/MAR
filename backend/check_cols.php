<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::getSchemaBuilder()->getColumnListing('usuarios_poa');
echo "Columns: " . implode(', ', $cols) . "\n\n";

$row = DB::table('usuarios_poa')->first();
echo "First row: " . json_encode($row) . "\n";
