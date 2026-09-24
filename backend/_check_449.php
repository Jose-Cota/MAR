<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$ro = DB::connection('poa_prod')->table('responsables_operativos')->where('responsable_operativo_id', 449)->first();
print_r($ro);
