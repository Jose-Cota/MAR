<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "--- acciones_sustantivas ---\n";
print_r(Schema::connection('poa_prod')->getColumnListing('acciones_sustantivas'));
echo "Max ID: " . DB::connection('poa_prod')->table('acciones_sustantivas')->max('accion_sustantiva_id') . "\n";
echo "Count: " . DB::connection('poa_prod')->table('acciones_sustantivas')->count() . "\n";

echo "--- actividades_sustantivas ---\n";
print_r(Schema::connection('poa_prod')->getColumnListing('actividades_sustantivas'));
echo "Max ID: " . DB::connection('poa_prod')->table('actividades_sustantivas')->max('id') . "\n";
echo "Count: " . DB::connection('poa_prod')->table('actividades_sustantivas')->count() . "\n";

$collisions = DB::connection('poa_prod')->select("
    SELECT a1.accion_sustantiva_id as id
    FROM acciones_sustantivas a1
    INNER JOIN actividades_sustantivas a2 ON a1.accion_sustantiva_id = a2.id
");
echo "Colliding IDs: " . count($collisions) . "\n";
