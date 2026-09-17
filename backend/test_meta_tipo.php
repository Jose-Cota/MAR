<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $meta = DB::connection('poa_prod')->select("SHOW COLUMNS FROM metas LIKE 'tipo'");
    print_r($meta);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
