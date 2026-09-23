<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

print_r(DB::table('actividades_sustantivas')->take(3)->get()->toArray());
print_r(DB::table('proyectos')->take(3)->get()->toArray());
