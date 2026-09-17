<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
try {
    $res = DB::connection('poa_prod')->select("SELECT usuario_poa_id, usuario, area_id FROM usuarios_poa WHERE usuario LIKE '%miguel%' OR usuario LIKE '%gutierrez%' LIMIT 5");
    echo "Miguel Gutierrez users:\n";
    print_r($res);
    
    // Also check for 'jose.cota' to prepare the exact SQL for him
    $res2 = DB::connection('poa_prod')->select("SELECT usuario_poa_id, usuario, area_id FROM usuarios_poa WHERE usuario LIKE '%jose%' OR usuario LIKE '%cota%' LIMIT 5");
    echo "\nCota Users:\n";
    print_r($res2);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
