<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$metas_2027 = DB::connection('poa_prod')->select("
    SELECT m.meta_id, m.tmc
    FROM metas m
    JOIN proyectos py ON m.proyecto_id = py.proyecto_id
    JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id
    JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
    JOIN ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id
    WHERE ej.ejercicio = 2027
");

$prog_count = 0;
$no_prog_count = 0;

foreach ($metas_2027 as $meta) {
    if ($meta->tmc == 1) {
        $metodo = 'Resultado = (Atendido/Programado)*100';
        $prog_count++;
    } else {
        $metodo = 'Resultado = (Atendido/Recibido)*100';
        $no_prog_count++;
    }

    DB::connection('poa_prod')->table('indicadores')
        ->where('meta_id', $meta->meta_id)
        ->update(['metodo_calculo' => $metodo]);
}

echo "Updated indicators for 2027:\n";
echo "Programables (tmc=1): $prog_count\n";
echo "No Programables (tmc!=1): $no_prog_count\n";
