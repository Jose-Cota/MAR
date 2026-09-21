<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Api\POAFichasController;

// Test "todas las areas"
$req = Request::create('/api/poa/fichas', 'GET', ['ejercicio_id' => 2027, 'area_id' => 'todas']);
$ctrl = new POAFichasController();
$resp = $ctrl->getFichas($req);
$data = json_decode($resp->getContent());

echo "Total proyectos: " . count($data) . "\n\n";

$totalAcciones = 0;
foreach ($data as $p) {
    $c = count($p->acciones ?? []);
    $totalAcciones += $c;
    if ($c > 0) {
        echo "Proyecto {$p->id}: {$c} acciones - " . ($p->nombre ?? $p->proyecto ?? '?') . "\n";
        echo "  Primera accion: " . ($p->acciones[0]->descripcion ?? $p->acciones[0]->denominacion ?? '?') . "\n";
    }
}

echo "\nTotal acciones encontradas: $totalAcciones\n";
