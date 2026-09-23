<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$risk = DB::table('riesgos')->first();
print_r($risk);

echo "Controles:\n";
print_r(DB::table('riesgo_controles')->where('riesgo_id', $risk->id ?? 0)->get()->toArray());

echo "Actividad_riesgo:\n";
print_r(DB::table('actividad_riesgo')->where('riesgo_id', $risk->id ?? 0)->get()->toArray());

echo "Accion_riesgo? Let's check table list\n";

