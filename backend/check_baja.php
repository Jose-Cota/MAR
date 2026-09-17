<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$proyectos = DB::connection('poa_prod')->table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->join('unidades_responsables_gastos', 'responsables_operativos.unidad_responsable_gasto_id', '=', 'unidades_responsables_gastos.unidad_responsable_gasto_id')
    ->join('ejercicios', 'unidades_responsables_gastos.ejercicio_id', '=', 'ejercicios.ejercicio_id')
    ->join('subprogramas', 'proyectos.subprograma_id', '=', 'subprogramas.subprograma_id')
    ->join('programas', 'subprogramas.programa_id', '=', 'programas.programa_id')
    ->where('unidades_responsables_gastos.numero', '11')
    ->where('responsables_operativos.numero', '24')
    ->whereIn('proyectos.numero', ['30', '31', '29'])
    ->select('ejercicios.ejercicio', 'proyectos.proyecto_id', 'programas.numero as pg', 'subprogramas.numero as sp', 'proyectos.numero as py', 'proyectos.nombre', 'proyectos.status')
    ->orderBy('ejercicios.ejercicio')
    ->orderBy('proyectos.numero')
    ->get();

foreach ($proyectos as $py) {
    echo "Ejercicio: {$py->ejercicio} | Clave: 11.24.{$py->pg}.{$py->sp}.{$py->py} | Nombre: {$py->nombre} | Status: {$py->status}\n";
}
