<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$empleados_sicope = DB::select('SELECT * FROM bd_11Mayo2026.empleados_sicope LIMIT 2');
$nominaEmpleado = DB::select('SELECT * FROM bd_11Mayo2026.nominaEmpleado LIMIT 2');
$catEmpleadosAll = DB::select('SELECT * FROM bd_11Mayo2026.catEmpleados LIMIT 2');

echo json_encode([
    'empleados_sicope' => $empleados_sicope,
    'nominaEmpleado' => $nominaEmpleado,
    'catEmpleadosAll' => $catEmpleadosAll
], JSON_PRETTY_PRINT);
