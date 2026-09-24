<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$count = DB::table('riesgos')->where('ejercicio_id', 2027)->count();
echo "Riesgos 2027 (ejercicio_id=2027): $count\n";
$count2 = DB::table('riesgos')->where('ejercicio_id', 2)->count();
echo "Riesgos 2027 (ejercicio_id=2): $count2\n";
