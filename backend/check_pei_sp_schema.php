<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::connection('poa_prod')->getSchemaBuilder()->getColumnListing('subprograma_pei_alineaciones');
print_r($columns);
