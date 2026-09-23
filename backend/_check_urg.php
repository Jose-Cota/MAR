<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

print_r(DB::table('unidades_responsables_gastos')->take(5)->get()->toArray());

// Also get the mapping of actions
// Try to find PRES-2026-A3
$query = "SELECT * FROM acciones_sustantivas WHERE descripcion LIKE '%Seguimiento al cumplimiento de acuerdos%'";
print_r(DB::select($query));

