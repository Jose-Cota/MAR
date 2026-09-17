<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Http\Request;
use App\Models\User;

$user = User::where('usuario', 'luis.flores')->first();
$controller = new DashboardController();

$request = Request::create('/api/dashboard', 'GET', ['ejercicio' => '2026']);
$request->setUserResolver(function () use ($user) {
    return $user;
});

$response = $controller->index($request);
$data = $response->getData();
echo "Total Proyectos (2026): {$data->total_proyectos}\n";
echo "Total URGs: {$data->total_urgs}\n";
foreach($data->fichas_por_urg as $urg) {
    echo "URG: {$urg->numero} - {$urg->nombre} (Proyectos: {$urg->proyectos})\n";
    foreach($urg->proyectosList as $py) {
        echo "  - PY {$py->numero}: RO {$py->ro_numero} ({$py->ro_nombre})\n";
    }
}
