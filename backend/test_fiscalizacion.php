<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
try {
    $res = DB::connection('poa_prod')->select("SELECT numero, nombre FROM unidades_medidas WHERE nombre LIKE '%Fiscalización%' OR numero = '' OR numero IS NULL LIMIT 5");
    print_r($res);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
