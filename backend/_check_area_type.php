<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$risk = DB::table('riesgos')->where('ejercicio_id', 17)->where('local_id', 'R1')->first();
echo "2026 Risk PRES R1 area_id: " . $risk->area_id . "\n";

$urg = DB::table('urgs')->where('id', $risk->area_id)->first();
echo "URG: " . ($urg->urg ?? 'None') . "\n";

$ro = DB::table('responsables_operativos')->where('id', $risk->area_id)->first();
echo "RO: " . ($ro->nombre ?? 'None') . "\n";
