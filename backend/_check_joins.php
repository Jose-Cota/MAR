<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$proy = DB::connection('poa_prod')->table('proyectos')->where('proyecto_id', 1767)->first();
echo "Proyecto 1767 subprograma_id: {$proy->subprograma_id}\n";

$sub = DB::connection('poa_prod')->table('subprogramas')->where('subprograma_id', $proy->subprograma_id)->first();
if(!$sub) echo "Subprograma NOT FOUND!\n";
else {
    echo "Subprograma found. programa_id: {$sub->programa_id}\n";
    $prog = DB::connection('poa_prod')->table('programas')->where('programa_id', $sub->programa_id)->first();
    if(!$prog) echo "Programa NOT FOUND!\n";
    else echo "Programa found.\n";
}
