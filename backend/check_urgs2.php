<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$default_urgs = DB::table('unidades_responsables_gastos')->take(3)->get();
echo "Default DB URGs:\n";
print_r($default_urgs->toArray());

$poa_urgs = DB::connection('poa_prod')->table('unidades_responsables_gastos')->take(3)->get();
echo "POA DB URGs:\n";
print_r($poa_urgs->toArray());
