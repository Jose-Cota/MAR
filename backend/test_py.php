<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$schema_py = DB::connection('poa_prod')->getSchemaBuilder()->getColumnListing('proyectos');
echo "proyectos columns: " . implode(', ', $schema_py) . "\n";

$py = DB::table('proyectos')->where('proyecto_id', 884)->first();
echo "Proyecto 884: " . json_encode($py) . "\n";

$sp = DB::table('subprogramas')->where('subprograma_id', $py->subprograma_id)->first();
echo "Subprograma: " . json_encode($sp) . "\n";
