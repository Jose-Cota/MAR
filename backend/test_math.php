<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $data = DB::connection('poa_prod')->select("
        SELECT mmp.numero as prog, mma.numero as alc
        FROM metas m
        JOIN meses_metas_programadas mmp ON m.meta_id = mmp.meta_id
        JOIN meses_metas_alcanzadas mma ON m.meta_id = mma.meta_id AND mma.mes_id = mmp.mes_id
        WHERE m.tipo = 'principal' AND mmp.numero > 0
        LIMIT 5
    ");
    print_r($data);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
