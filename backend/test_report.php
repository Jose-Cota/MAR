<?php
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ReporteController;

try {
    $controller = new ReporteController();
    $request = new Request();
    $response = $controller->tablaProyectos($request);
    echo "SUCCESS: Status code is " . $response->getStatusCode() . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
