<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ps = Illuminate\Support\Facades\DB::table('proyectos')->where('ejercicio_id', 19)->get();
echo "Proyectos en 2027: " . $ps->count() . "\n";
if ($ps->count() > 0) {
    print_r($ps->first());
}
