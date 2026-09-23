<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$counts = DB::table('riesgos')
    ->select('ejercicios.ejercicio', DB::raw('count(*) as total'))
    ->join('ejercicios', 'ejercicios.ejercicio_id', '=', 'riesgos.ejercicio_id')
    ->groupBy('ejercicios.ejercicio')
    ->orderBy('ejercicios.ejercicio')
    ->get();

echo "Conteo de riesgos por año en la Base de Datos:\n";
foreach ($counts as $count) {
    echo "Año " . $count->ejercicio . ": " . $count->total . " riesgos\n";
}
