<?php
ini_set('memory_limit', '256M');
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$proyectos2027 = DB::table('proyectos')->where('ejercicio_id', 19)->pluck('proyecto_id');

$oldIds = DB::table('actividades_sustantivas')->whereIn('proyecto_id', $proyectos2027)->pluck('id');
if($oldIds->count() > 0) {
    echo "Found " . $oldIds->count() . " records in actividades_sustantivas for 2027. Deleting...\n";
    DB::table('actividad_riesgo')->whereIn('actividad_sustantiva_id', $oldIds)->delete();
    DB::table('actividades_sustantivas')->whereIn('proyecto_id', $proyectos2027)->delete();
    echo "Done.\n";
} else {
    echo "No records found in actividades_sustantivas for 2027.\n";
}
