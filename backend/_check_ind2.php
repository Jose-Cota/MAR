<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$inds = DB::table('riesgo_indicadores')->where('formula', 'like', '%N / D%')->take(5)->get();
foreach($inds as $i) {
    echo "ID: {$i->id}\n";
    echo "Nombre: {$i->nombre}\n";
    echo "Formula: {$i->formula}\n";
    echo "----------\n";
}
