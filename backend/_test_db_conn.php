<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

try {
    DB::connection('poa_prod')->getPdo();
    echo "Connection poa_prod OK\n";
} catch (\Exception $e) {
    echo "Connection poa_prod FAIL: " . $e->getMessage() . "\n";
}
