<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$acts = DB::connection('poa_prod')->table('actividades_sustantivas')
    ->where('descripcion', 'like', '%Garantizar la autonomía%')
    ->where('es_resumida', 1)
    ->get();
    
foreach($acts as $a) {
    echo "ID: {$a->id} | Proy: {$a->proyecto_id} | Num: {$a->numero}\n";
}
