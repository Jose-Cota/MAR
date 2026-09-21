<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DIAGNÓSTICO COMPLETO POA 2027 ===\n\n";

// 1. Ejercicio 2027
$ej = DB::table('ejercicios')->where('ejercicio', 2027)->first();
echo "Ejercicio 2027: ej_id=" . ($ej ? $ej->ejercicio_id : 'NO EXISTE') . "\n\n";
if (!$ej) { die("No hay ejercicio 2027\n"); }

$ej_id = $ej->ejercicio_id;

// 2. Proyectos para 2027
$totalProyectos = DB::table('proyectos')->where('ejercicio_id', $ej_id)->count();
echo "Total proyectos en 2027: $totalProyectos\n\n";

// 3. Muestra de responsables_operativos para esos proyectos
$sampleRO = DB::table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->where('proyectos.ejercicio_id', $ej_id)
    ->select('responsables_operativos.*')
    ->take(3)->get();

echo "Sample responsables_operativos para proyectos 2027:\n";
foreach ($sampleRO as $ro) {
    echo "  ro_id={$ro->responsable_operativo_id}, urg_id={$ro->unidad_responsable_gasto_id}, ej_id={$ro->ejercicio_id}, nombre={$ro->nombre}\n";
}
echo "\n";

// 4. ¿Existe urg_id=564 en unidades_responsables_gastos?
$urg564 = DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', 564)->first();
echo "URG ID 564 en unidades_responsables_gastos: " . ($urg564 ? json_encode($urg564) : 'NO EXISTE') . "\n\n";

// 5. ¿Qué URGs existen para ejercicio 19 y cuántas hay?
$urgCount = DB::table('unidades_responsables_gastos')->where('ejercicio_id', $ej_id)->count();
$maxId = DB::table('unidades_responsables_gastos')->max('unidad_responsable_gasto_id');
echo "URGs para ej_id=$ej_id: $urgCount, MAX ID: $maxId\n\n";

// 6. Muestra de URGs con ID alto (donde está 564)
$highURGs = DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', '>=', 560)->get();
echo "URGs con ID >= 560:\n";
foreach ($highURGs as $u) {
    echo "  id={$u->unidad_responsable_gasto_id}, ej_id={$u->ejercicio_id}, num={$u->numero}, nombre={$u->nombre}\n";
}
echo "\n";

// 7. Acciones sustantivas por proyecto del URG "20" usando numero
$urgNro20 = DB::table('unidades_responsables_gastos')->where('numero', '20')->where('ejercicio_id', $ej_id)->first();
echo "URG numero='20' para ej_id=$ej_id: " . ($urgNro20 ? "id={$urgNro20->unidad_responsable_gasto_id}, nombre={$urgNro20->nombre}" : 'NO ENCONTRADA') . "\n\n";

// 8. Si existe, buscar proyectos vinculados
if ($urgNro20) {
    $proyectosURG20 = DB::table('proyectos')
        ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
        ->where('proyectos.ejercicio_id', $ej_id)
        ->where('responsables_operativos.unidad_responsable_gasto_id', $urgNro20->unidad_responsable_gasto_id)
        ->count();
    echo "Proyectos para URG 20 (id={$urgNro20->unidad_responsable_gasto_id}): $proyectosURG20\n";
}

// 9. Total acciones sustantivas disponibles
$totalAcciones = DB::table('acciones_sustantivas')->count();
$totalActividades = DB::table('actividades_sustantivas')->count();
echo "\nTotal acciones_sustantivas: $totalAcciones\n";
echo "Total actividades_sustantivas: $totalActividades\n";

// 10. Acciones para proyectos de 2027
$proyectoIds = DB::table('proyectos')->where('ejercicio_id', $ej_id)->pluck('proyecto_id');
$accionesEj = DB::table('acciones_sustantivas')->whereIn('proyecto_id', $proyectoIds)->count();
$actividadesEj = DB::table('actividades_sustantivas')->whereIn('proyecto_id', $proyectoIds)->count();
echo "Acciones sustantivas para proyectos 2027: $accionesEj\n";
echo "Actividades sustantivas para proyectos 2027: $actividadesEj\n";
