<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
try {
    $res = DB::connection('poa_prod')->select("SHOW COLUMNS FROM unidades_medidas");
    print_r($res);
} catch (Exception $e) {
    try {
        $res = DB::connection('poa_prod')->select("SHOW COLUMNS FROM unidad_medidas");
        print_r($res);
    } catch (Exception $e2) {
        echo "Error: " . $e2->getMessage();
    }
}
