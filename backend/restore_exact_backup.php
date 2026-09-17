<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::connection('poa_prod')->beginTransaction();
try {
    $proyectos_backup = DB::connection('poa_prod')->table('proyectos_backup_21082026')
        ->select('proyecto_id', 'numero')
        ->get();

    $count = 0;
    foreach ($proyectos_backup as $backup) {
        // Only update 2026 projects, but since backup only has 2026 (or we can just match by ID)
        DB::connection('poa_prod')->table('proyectos')
            ->where('proyecto_id', $backup->proyecto_id)
            ->update(['numero' => $backup->numero]);
        $count++;
    }

    DB::connection('poa_prod')->commit();
    echo "Restored exact 'numero' for $count projects from proyectos_backup_21082026.\n";
} catch (\Exception $e) {
    DB::connection('poa_prod')->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
