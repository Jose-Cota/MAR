<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$schema_acc = DB::connection('poa_prod')->getSchemaBuilder()->getColumnListing('acciones_sustantivas');
echo "acciones_sustantivas columns: " . implode(', ', $schema_acc) . "\n";

$acc = DB::connection('poa_prod')->table('acciones_sustantivas')
    ->where('proyecto_id', 884)
    ->get();
echo "Acciones para proyecto 884: " . json_encode($acc) . "\n";

$countAcc = DB::table('acciones_sustantivas')->count();
echo "Total acciones_sustantivas: $countAcc\n";

