<?php
ini_set('memory_limit', '256M');
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

// Ejercicios 2026 y 2027
$ej2026 = DB::table('ejercicios')->where('ejercicio', 2026)->first();
$ej2027 = DB::table('ejercicios')->where('ejercicio', 2027)->first();
echo "Ejercicio 2026: ejercicio_id=" . ($ej2026->ejercicio_id ?? 'NO ENCONTRADO') . "\n";
echo "Ejercicio 2027: ejercicio_id=" . ($ej2027->ejercicio_id ?? 'NO ENCONTRADO') . "\n";

// Proyectos disponibles en esos ejercicios
$ejIds = array_filter([$ej2026->ejercicio_id ?? null, $ej2027->ejercicio_id ?? null]);
echo "\n=== Proyectos en 2026/2027 ===\n";
$p = DB::table('proyectos')->whereIn('ejercicio_id', $ejIds)->select('proyecto_id','ejercicio_id','nombre','responsable_operativo_id','subprograma_id')->limit(10)->get();
foreach ($p as $r) echo json_encode($r) . "\n";

// ¿Cómo se relacionan proyectos con unidades/areas?
echo "\n=== Relación proyecto → area en riesgos ===\n";
echo "(riesgos.area_id es numérico, veamos si proyectos tiene área)\n";
// Buscar columnas de proyectos que tengan "ur" o "area"
$cols = DB::select('DESCRIBE proyectos');
foreach ($cols as $c) {
    if (stripos($c->Field, 'ur') !== false || stripos($c->Field, 'area') !== false || stripos($c->Field, 'unidad') !== false) {
        echo "  >> {$c->Field} | {$c->Type}\n";
    }
}

// Muestra de cómo el sistema MAR relaciona area_id con proyectos
echo "\n=== ResponsableOperativo estructura ===\n";
$cols2 = DB::select('DESCRIBE responsables_operativos');
foreach ($cols2 as $c) echo "  {$c->Field} | {$c->Type}\n";
$ros = DB::table('responsables_operativos')->limit(3)->get();
foreach ($ros as $r) echo json_encode($r) . "\n";

// ¿Cómo se linkea area_id en riesgos?
echo "\n=== Estructura areas ===\n";
$cols3 = DB::select('DESCRIBE areas');
foreach ($cols3 as $c) echo "  {$c->Field} | {$c->Type}\n";
$areas = DB::table('areas')->limit(5)->get();
foreach ($areas as $a) echo json_encode($a) . "\n";
