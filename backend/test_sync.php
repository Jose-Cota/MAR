<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();
    $r = App\Models\Riesgo::create([
        'area_id' => 1,
        'ejercicio_id' => 2027,
        'local_id' => 'R2',
        'riesgo' => 'test 2'
    ]);
    echo "Created riesgo ID: " . $r->id . "\n";
    echo "Starting sync...\n";
    $r->actividades()->sync([1]);
    echo "Sync done.\n";
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
