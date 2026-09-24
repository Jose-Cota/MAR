<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$roIdsPoa = DB::connection('poa_prod')->table('responsables_operativos')
    ->where('unidad_responsable_gasto_id', 564)
    ->pluck('responsable_operativo_id')
    ->toArray();
print_r($roIdsPoa);
