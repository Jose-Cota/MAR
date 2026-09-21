<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$c17 = DB::table('proyectos')->where('ejercicio_id', 17)->count();
echo "Proyectos con ejercicio_id = 17: $c17\n";

$c2027 = DB::table('proyectos')->where('ejercicio_id', 2027)->count();
echo "Proyectos con ejercicio_id = 2027: $c2027\n";

$all = DB::table('proyectos')->select('ejercicio_id', DB::raw('count(*) as c'))->groupBy('ejercicio_id')->get();
echo "Agrupados por ejercicio_id:\n";
print_r($all->toArray());
