<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ejercicios = DB::table('ejercicios')->whereIn('ejercicio', [2026, 2027])->pluck('ejercicio_id')->toArray();
if (empty($ejercicios)) {
    $ejercicios = [18, 19];
}

$riesgos = DB::table('riesgos')->whereIn('ejercicio_id', $ejercicios)->count();
$controles = DB::table('riesgo_controles')
    ->join('riesgos', 'riesgos.id', '=', 'riesgo_controles.riesgo_id')
    ->whereIn('riesgos.ejercicio_id', $ejercicios)
    ->count();
$indicadores = DB::table('riesgo_indicadores')
    ->join('riesgos', 'riesgos.id', '=', 'riesgo_indicadores.riesgo_id')
    ->whereIn('riesgos.ejercicio_id', $ejercicios)
    ->count();
$links = DB::table('actividad_riesgo')
    ->join('riesgos', 'riesgos.id', '=', 'actividad_riesgo.riesgo_id')
    ->whereIn('riesgos.ejercicio_id', $ejercicios)
    ->count();

echo "Conteo de la base de datos (2026 y 2027):\n";
echo "Riesgos: $riesgos\n";
echo "Controles: $controles\n";
echo "Indicadores: $indicadores\n";
echo "Links Actividad-Riesgo: $links\n";

// Sample of links
echo "\nMuestra de 5 links actividad_riesgo:\n";
print_r(DB::table('actividad_riesgo')
    ->join('riesgos', 'riesgos.id', '=', 'actividad_riesgo.riesgo_id')
    ->select('actividad_riesgo.*', 'riesgos.local_id', 'riesgos.ejercicio_id')
    ->take(5)
    ->get()->toArray()
);

