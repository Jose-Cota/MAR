<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::table('proyectos')->whereIn('proyecto_id', [880, 881, 893])->update(['ejercicio_id' => 99]);
echo "Moved extra projects to ejercicio 99.\n";
