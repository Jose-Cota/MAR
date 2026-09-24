<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$proys = DB::table('proyectos')->whereIn('proyecto_id', [1780, 1781, 1782])->get();
foreach($proys as $p) {
    echo "Proyecto {$p->proyecto_id} tiene RO {$p->responsable_operativo_id}\n";
    $ro = DB::table('responsables_operativos')->where('responsable_operativo_id', $p->responsable_operativo_id)->first();
    if($ro) {
        echo "  RO nombre: {$ro->nombre}, URG ID: {$ro->unidad_responsable_gasto_id}\n";
    } else {
        echo "  RO NO ENCONTRADO\n";
    }
}
