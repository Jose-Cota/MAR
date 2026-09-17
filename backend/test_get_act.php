<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$act = DB::connection('poa_prod')
            ->table('acciones_sustantivas')
            ->where('proyecto_id', 884)
            ->select('accion_sustantiva_id as id', 'proyecto_id', 'descripcion', 'numero as orden')
            ->orderBy('numero')
            ->get();
echo json_encode($act);
