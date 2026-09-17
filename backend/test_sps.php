<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sps = DB::connection('poa_prod')->table('subprogramas')->get();
foreach ($sps as $sp) {
    echo "SP ID: {$sp->subprograma_id}, Prog ID: {$sp->programa_id}, Num: {$sp->numero}, Name: {$sp->nombre}\n";
}
