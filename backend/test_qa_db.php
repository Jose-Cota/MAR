<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
try {
    // Check QA users table
    $users = DB::select("SELECT * FROM users WHERE email LIKE '%cota%' OR name LIKE '%cota%' LIMIT 5");
    echo "\nQA Users (table 'users'):\n";
    print_r($users);

    // Check QA usuarios_poa table
    $u_poa = DB::select("SELECT usuario_poa_id, usuario, area_id FROM usuarios_poa WHERE usuario LIKE '%cota%' LIMIT 5");
    echo "\nQA Users (table 'usuarios_poa'):\n";
    print_r($u_poa);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
