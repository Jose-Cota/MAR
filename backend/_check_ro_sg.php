<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$proys = DB::table('proyectos')->whereIn('proyecto_id', [1768, 1769, 1770])->get();
foreach($proys as $p) {
    $ro = DB::table('responsables_operativos')->where('responsable_operativo_id', $p->responsable_operativo_id)->first();
    echo "SG Proy {$p->proyecto_id} tiene RO {$p->responsable_operativo_id} (ejercicio: {$ro->ejercicio_id})\n";
}
