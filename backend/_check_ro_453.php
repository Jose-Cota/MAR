<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$ro = DB::table('responsables_operativos')->where('responsable_operativo_id', 453)->first();
echo "RO 453 tiene ejercicio_id: {$ro->ejercicio_id}\n";
