<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = app()->make('App\Http\Controllers\Api\POAFichasController');
$f = json_decode(json_encode($c->getFichas(request()->merge(['ejercicio_id'=>'2027', 'area_id'=>'8']))->getData()), true);
foreach($f as $p) {
    echo "Proyecto {$p['nombre']}:\n";
    foreach($p['acciones'] as $a) {
        echo "- {$a['descripcion']}\n";
    }
}
