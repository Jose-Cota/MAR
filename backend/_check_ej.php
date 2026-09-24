<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$ej = DB::connection('poa_prod')->table('ejercicios')->get();
foreach($ej as $e) echo "{$e->ejercicio_id} -> {$e->ejercicio}\n";
