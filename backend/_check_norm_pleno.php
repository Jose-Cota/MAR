<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$urgs = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', 19)->where('unidad_responsable_gasto_id', '>=', 240)->get();
$normalizar = function ($s) {
    $s = mb_strtolower(trim($s));
    $s = preg_replace('/[áàäâ]/u', 'a', $s);
    $s = preg_replace('/[éèëê]/u', 'e', $s);
    $s = preg_replace('/[íìïî]/u', 'i', $s);
    $s = preg_replace('/[óòöô]/u', 'o', $s);
    $s = preg_replace('/[úùüû]/u', 'u', $s);
    return $s;
};
foreach ($urgs as $u) {
    if ($normalizar($u->nombre) === 'pleno') {
        echo "Found: " . $u->unidad_responsable_gasto_id . "\n";
    }
}
