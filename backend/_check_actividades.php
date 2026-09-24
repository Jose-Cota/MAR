<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$a = DB::table('actividades_sustantivas')->whereIn('proyecto_id', [1768, 1769, 1770])->get();
echo 'SG 2027 tiene ' . count($a) . ' actividades en actividades_sustantivas';
