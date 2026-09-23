<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$data = json_decode(file_get_contents('C:/Cota/MAR/Respaldo_MAR_TECDMX_2026-09-23.json'), true);
$risks2027 = array_filter($data['risks'] ?? [], function($r) { return strpos($r['id'], '2027') !== false; });

foreach ($risks2027 as $r) {
    $db = DB::table('riesgos')->where('local_id', $r['localId'])->where('ejercicio_id', 19);
    // actually local_id could be duplicate across areas, we should check by area!
    // But since localId is just "R1", "R2", we need to check area_id too!
}
