<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$proys_a_arreglar = [1771, 1773, 1774, 1780, 1781, 1782, 1783, 1784, 1797, 1804, 1805, 1806];

foreach($proys_a_arreglar as $pid) {
    // Buscar un RO valido del area correspondiente
    // Mejor, encontremos la relacion en POAFichasController:
    // Sabemos que DGJ tiene la URG estructural 569 y su RO es 980.
    if(in_array($pid, [1780, 1781, 1782])) { // DGJ
        DB::table('proyectos')->where('proyecto_id', $pid)->update(['responsable_operativo_id' => 980]);
    }
}
echo "DGJ fijado\n";
