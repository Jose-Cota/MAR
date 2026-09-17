<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$schema_pei = DB::connection('poa_prod')->getSchemaBuilder()->getColumnListing('pei_programas');
echo "pei_programas columns: " . implode(', ', $schema_pei) . "\n";

$schema_act = DB::connection('poa_prod')->getSchemaBuilder()->getColumnListing('actividades_sustantivas');
echo "actividades_sustantivas columns: " . implode(', ', $schema_act) . "\n";

$pei = DB::connection('poa_prod')->table('pei_programas')->first();
echo "Sample PEI Programa: " . json_encode($pei) . "\n";

$act = DB::connection('poa_prod')->table('actividades_sustantivas')->first();
echo "Sample Actividad: " . json_encode($act) . "\n";
