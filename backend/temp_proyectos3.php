<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Projects in 2026 DB
$projs2026 = DB::connection('poa_prod')->table('proyectos')->orderByRaw('CAST(numero AS INTEGER)')->get(['proyecto_id', 'numero', 'nombre']);

echo "Projects in current DB (2026):\n";
foreach($projs2026 as $p) {
    echo "PY: {$p->numero} | ID: {$p->proyecto_id} | {$p->nombre}\n";
}

echo "-------------------------\n";

// Try 2025 DB if exists
try {
    $projs2025 = DB::connection('poa_prod')->table('0201sadpyrf_poa2025.proyectos')->orderByRaw('CAST(numero AS INTEGER)')->get(['proyecto_id', 'numero', 'nombre']);
    echo "Projects in 2025 DB:\n";
    foreach($projs2025 as $p) {
        echo "PY: {$p->numero} | ID: {$p->proyecto_id} | {$p->nombre}\n";
    }
} catch (\Exception $e) {
    echo "Could not load 2025 DB: " . $e->getMessage() . "\n";
}
