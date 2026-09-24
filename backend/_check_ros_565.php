<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$ros = DB::connection('poa_prod')->table('responsables_operativos')->where('unidad_responsable_gasto_id', 565)->get();
print_r($ros);
