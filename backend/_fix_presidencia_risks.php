<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$affected = DB::table('riesgos')->where('ejercicio_id', 19)->where('area_id', 1)->update(['area_id' => 970]);
echo "Updated $affected risks to area_id 970 (Presidencia).\n";
