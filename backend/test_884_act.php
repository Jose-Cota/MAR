<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$acts = DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', 884)->get();
echo "884 Activities count: " . count($acts) . "\n";
