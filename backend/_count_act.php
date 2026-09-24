<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$total = DB::table('acciones_sustantivas')->count();
echo "Total acciones_sustantivas: $total\n";
$res = DB::table('acciones_sustantivas as a')->join('proyectos as p', 'p.proyecto_id', '=', 'a.proyecto_id')->join('ejercicios as e', 'e.ejercicio_id', '=', 'p.ejercicio_id')->select('e.ejercicio', DB::raw('count(*) as count'))->groupBy('e.ejercicio')->get();
foreach($res as $r) {
    echo "Ejercicio {$r->ejercicio}: {$r->count}\n";
}
