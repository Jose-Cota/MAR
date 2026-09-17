<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ProyectoController;
use Illuminate\Http\Request;

// We'll just instantiate the controller and see if we can get the getActividades manually by making it public or using reflection, OR just query what it queries exactly.
$proyectoId = 1438;
$actividades = DB::connection('poa_prod')
            ->table('acciones_sustantivas')
            ->where('proyecto_id', $proyectoId)
            ->select('accion_sustantiva_id as id', 'proyecto_id', 'descripcion', 'numero as orden')
            ->orderBy('numero')
            ->get();
            
echo "Controller query returned: " . count($actividades) . "\n";
echo json_encode($actividades) . "\n";
