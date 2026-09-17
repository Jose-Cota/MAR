<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$projects = DB::connection('poa_prod')
    ->table('proyectos as py')
    ->join('ejercicios as ej', 'py.ejercicio_id', '=', 'ej.ejercicio_id', 'left outer')
    ->select('py.proyecto_id', 'py.nombre')
    ->get();

foreach ($projects as $py) {
    $c = DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $py->proyecto_id)->count();
    if ($c == 0) {
        // echo "Project {$py->proyecto_id} ({$py->nombre}) has 0 activities.\n";
    }
}

// Check how many have > 0
$withActs = DB::connection('poa_prod')->table('acciones_sustantivas')->distinct('proyecto_id')->count('proyecto_id');
echo "Projects with >0 acts: $withActs\n";
