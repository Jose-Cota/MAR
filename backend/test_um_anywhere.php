<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$um = DB::connection('poa_prod')->table('unidades_medidas')->where('unidad_medida_id', 1047)->first();
print_r($um);
if (!$um) {
    echo "1047 does not exist ANYWHERE in unidades_medidas.\n";
}

$um2 = DB::connection('poa_prod')->table('unidades_medidas')->where('nombre', 'LIKE', '%Asunto%')->get();
// print_r($um2);
