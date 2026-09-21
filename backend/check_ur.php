<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ur = DB::table('unidades_responsables_gasto')->where('denominacion', 'like', '%Especializada%')->first();
echo "UR from unidades_responsables_gasto:\n";
print_r($ur);
