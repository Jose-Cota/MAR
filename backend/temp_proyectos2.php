<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find projects for 2025
$projs2025 = DB::connection('poa_prod')->table('proyectos')->where('ejercicio', 2025)->orderByRaw('CAST(numero AS INTEGER)')->get(['proyecto_id', 'numero', 'nombre']);

echo "Projects 2025:\n";
foreach($projs2025 as $p) {
    echo "PY: {$p->numero} | ID: {$p->proyecto_id} | {$p->nombre}\n";
}

echo "-------------------------\n";

// Find projects for 2026
$projs2026 = DB::connection('poa_prod')->table('proyectos')->where('ejercicio', 2026)->orderByRaw('CAST(numero AS INTEGER)')->get(['proyecto_id', 'numero', 'nombre']);

echo "Projects 2026:\n";
foreach($projs2026 as $p) {
    echo "PY: {$p->numero} | ID: {$p->proyecto_id} | {$p->nombre}\n";
}
