<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$roles = DB::connection('poa_prod')->table('roles')->select('name')->distinct()->get();
foreach ($roles as $e) {
    echo "Role: '" . $e->name . "'\n";
}
