<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Api\POAFichasController;

try {
    $req = Request::create('/api/poa/fichas', 'GET', ['ejercicio_id' => 17, 'area_id' => 'todas']);
    $controller = new POAFichasController();
    $resp = $controller->getFichas($req);
    echo "Response status: " . $resp->getStatusCode() . "\n";
    if ($resp->getStatusCode() !== 200) {
        echo $resp->getContent();
    }
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
