<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$relations = DB::table('actividad_riesgo')->limit(10)->get();
print_r($relations);

$first = $relations->first();
if ($first) {
    echo "Is it in acciones_sustantivas?\n";
    print_r(DB::table('acciones_sustantivas')->where('accion_sustantiva_id', $first->actividad_sustantiva_id)->first());

    echo "Is it in actividades_sustantivas?\n";
    print_r(DB::table('actividades_sustantivas')->where('id', $first->actividad_sustantiva_id)->first());
}
