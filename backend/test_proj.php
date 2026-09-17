<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$py = DB::connection('poa_prod')->table('proyectos')->where('nombre', 'like', '%Operación y control de pago de nóminas%')->orderBy('proyecto_id', 'desc')->first();
if (!$py) {
    echo "Project not found\n";
    exit;
}
echo "Proyecto ID: " . $py->proyecto_id . "\n";

$metas = DB::connection('poa_prod')->table('metas')->where('proyecto_id', $py->proyecto_id)->get();
echo "Metas count: " . count($metas) . "\n";

$metaIds = $metas->pluck('meta_id')->all();
if (empty($metaIds)) {
    echo "No metas\n";
    exit;
}

$inds = DB::connection('poa_prod')->table('indicadores')->whereIn('meta_id', $metaIds)->get();
echo "Indicadores (by meta_id in metas) count: " . count($inds) . "\n";

$indsPy = DB::connection('poa_prod')->table('indicadores')->where('proyecto_id', $py->proyecto_id)->get();
echo "Indicadores (by proyecto_id) count: " . count($indsPy) . "\n";

echo "Metas:\n";
foreach($metas as $m) {
    echo " - ID: $m->meta_id, tipo: $m->tipo, um: $m->unidad_medida_id\n";
}

echo "Indicadores (by proyecto_id):\n";
foreach($indsPy as $i) {
    echo " - ID: $i->indicador_id, meta_id: $i->meta_id, id_metac: $i->id_metac, nombre: $i->nombre\n";
}
