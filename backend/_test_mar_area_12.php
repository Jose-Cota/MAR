<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$area = DB::table('0201sadpyrf_mar2026.areas')->where('area_id', 12)->first();
echo "MAR AREA 12: " . $area->nombre . "\n";
