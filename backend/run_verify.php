<?php
$py = DB::connection('poa_prod')->table('proyectos as py')
    ->join('ejercicios as e', 'py.ejercicio_id', '=', 'e.ejercicio_id')
    ->select('py.proyecto_id')
    ->where('py.numero', '08')
    ->where('e.ejercicio', '2027')
    ->first();

if (!$py) {
    die("Proyecto 08 no encontrado\n");
}

$id = $py->proyecto_id;

// Update status to verify
DB::connection('poa_prod')
    ->table('proyectos')
    ->where('proyecto_id', $id)
    ->update(['status' => 'Verificado']);

echo "Cambiado a verificado. Enviando correo...\n";

// Use reflection to call private method
$controller = app(\App\Http\Controllers\Api\ProyectoController::class);
$reflection = new \ReflectionClass($controller);
$method = $reflection->getMethod('checkAndSendEmail');
$method->setAccessible(true);

$method->invokeArgs($controller, [
    $id, 
    'Verificado', 
    'El proyecto ha sido Verificado y está listo para su impresión, firma y envío.'
]);

echo "Proceso terminado.\n";
