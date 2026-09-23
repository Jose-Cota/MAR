<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Delete old risks that have ejercicio_id as 2026 or 2027 instead of 18 or 19
$oldIds = DB::table('riesgos')->whereIn('ejercicio_id', [2026, 2027])->pluck('id')->toArray();

if (!empty($oldIds)) {
    DB::table('riesgo_controles')->whereIn('riesgo_id', $oldIds)->delete();
    DB::table('riesgo_indicadores')->whereIn('riesgo_id', $oldIds)->delete();
    DB::table('actividad_riesgo')->whereIn('riesgo_id', $oldIds)->delete();
    DB::table('riesgos')->whereIn('id', $oldIds)->delete();
    echo "Deleted " . count($oldIds) . " old risks with literal year in ejercicio_id.\n";
} else {
    echo "No old risks found with literal year in ejercicio_id.\n";
}

// Check how many risks remain with 18 and 19
$newIds = DB::table('riesgos')->whereIn('ejercicio_id', [18, 19])->count();
echo "Remaining risks with correct ejercicio_id (18, 19): $newIds\n";

// What if the old ones had 18 and 19 but were created before today?
$oldValidIds = DB::table('riesgos')->whereIn('ejercicio_id', [18, 19])->whereDate('created_at', '<', date('Y-m-d'))->pluck('id')->toArray();
if (!empty($oldValidIds)) {
    DB::table('riesgo_controles')->whereIn('riesgo_id', $oldValidIds)->delete();
    DB::table('riesgo_indicadores')->whereIn('riesgo_id', $oldValidIds)->delete();
    DB::table('actividad_riesgo')->whereIn('riesgo_id', $oldValidIds)->delete();
    DB::table('riesgos')->whereIn('id', $oldValidIds)->delete();
    echo "Deleted " . count($oldValidIds) . " old risks with correct ejercicio_id but old created_at.\n";
}
