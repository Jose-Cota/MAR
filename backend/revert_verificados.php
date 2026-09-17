<?php
$proyectos = DB::connection('poa_prod')->table('proyectos as py')
    ->join('ejercicios as e', 'py.ejercicio_id', '=', 'e.ejercicio_id')
    ->select('py.proyecto_id', 'py.numero')
    ->where('e.ejercicio', '2027')
    ->where('py.status', 'Verificado')
    ->get();

if ($proyectos->isEmpty()) {
    echo "No hay proyectos verificados en 2027.\n";
    exit;
}

foreach ($proyectos as $py) {
    DB::connection('poa_prod')
        ->table('proyectos')
        ->where('proyecto_id', $py->proyecto_id)
        ->update([
            'status' => 'Validacion',
            'fecha_verificacion' => null
        ]);
        
    echo "Proyecto {$py->numero} revertido a Validacion.\n";
}

echo "Proceso terminado.\n";
