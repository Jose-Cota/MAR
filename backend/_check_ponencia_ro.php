<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$ros = DB::connection('poa_prod')->table('responsables_operativos')->where('nombre', 'like', '%Ponencia%')->where('ejercicio_id', 19)->get();
print_r($ros);
