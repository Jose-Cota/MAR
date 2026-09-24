<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = app()->make('App\Http\Controllers\Api\POAFichasController');
for($i=1; $i<=25; $i++) {
    $req = request()->merge(['ejercicio_id'=>'2027', 'area_id'=>(string)$i]);
    $res = $c->getFichas($req)->getData();
    echo "Area $i: " . count($res) . " projects\n";
}
