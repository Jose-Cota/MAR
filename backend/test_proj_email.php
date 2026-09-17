<?php
// Proyecto 08 of 2027
$py = DB::connection('poa_prod')->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('ejercicios as e', 'py.ejercicio_id', '=', 'e.ejercicio_id')
    ->select('py.proyecto_id', 'urg.unidad_responsable_gasto_id', 'py.numero')
    ->where('py.numero', '08')
    ->where('e.ejercicio', '2027')
    ->first();

if (!$py) {
    echo "Proyecto 08 2027 no encontrado.\n";
    exit;
}

echo "Proyecto encontrado, URG ID: {$py->unidad_responsable_gasto_id}\n";

$validadores = \App\Models\User::whereHas('roles', function($q) {
        $q->whereIn('name', ['Validador', 'Capturador']);
    })
    ->whereHas('unidadesResponsables', function($q) use ($py) {
        $q->where('unidades_responsables_gastos.unidad_responsable_gasto_id', $py->unidad_responsable_gasto_id);
    })
    ->whereNotNull('correo')
    ->get();

echo "Usuarios encontrados para enviar correo: " . $validadores->count() . "\n";
foreach ($validadores as $v) {
    echo " - {$v->usuario} ({$v->correo})\n";
}
