<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ro = Illuminate\Support\Facades\DB::table('responsables_operativos')->where('responsable_operativo_id', 965)->first();
print_r($ro);
