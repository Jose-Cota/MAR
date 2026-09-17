<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::connection('poa_prod')->beginTransaction();
try {
    $deleted = DB::connection('poa_prod')->table('proyectos')
        ->where('status', 'BAJA')
        ->orWhere('numero', '99')
        ->delete();

    DB::connection('poa_prod')->commit();
    echo "Successfully deleted $deleted BAJA projects from the database.\n";
} catch (\Exception $e) {
    DB::connection('poa_prod')->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
