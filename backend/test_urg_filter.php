<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Api\POAFichasController;

$ctrl = new POAFichasController();

// Test area 20 = "Unidad Especializada de Procedimientos Sancionadores"
echo "=== AREA 20 (Unidad Especializada) ===\n";
$req = Request::create('/api/poa/fichas', 'GET', ['ejercicio_id' => 2027, 'area_id' => '20']);
$resp = $ctrl->getFichas($req);
$data = json_decode($resp->getContent());
echo "Proyectos: " . count($data) . "\n";
foreach ($data as $p) {
    echo "  {$p->id}: " . ($p->nombre ?? '?') . " - " . count($p->acciones) . " acciones\n";
}

// Test area 13 = "Unidad de Servicios Informaticos"
echo "\n=== AREA 13 (Servicios Informáticos) ===\n";
$req2 = Request::create('/api/poa/fichas', 'GET', ['ejercicio_id' => 2027, 'area_id' => '13']);
$resp2 = $ctrl->getFichas($req2);
$data2 = json_decode($resp2->getContent());
echo "Proyectos: " . count($data2) . "\n";
foreach ($data2 as $p) {
    echo "  {$p->id}: " . ($p->nombre ?? '?') . " - " . count($p->acciones) . " acciones\n";
}

// Test todas
echo "\n=== TODAS ===\n";
$req3 = Request::create('/api/poa/fichas', 'GET', ['ejercicio_id' => 2027, 'area_id' => 'todas']);
$resp3 = $ctrl->getFichas($req3);
$data3 = json_decode($resp3->getContent());
echo "Total proyectos: " . count($data3) . "\n";
$totalAcciones = array_sum(array_map(fn($p) => count($p->acciones ?? []), $data3));
echo "Total acciones: $totalAcciones\n";
