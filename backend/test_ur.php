<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $res = Illuminate\Support\Facades\DB::connection('poa_prod')->select("SELECT * FROM unidades_responsables_gastos WHERE numero = '01'");
    print_r($res);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
