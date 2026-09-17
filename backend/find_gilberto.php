<?php
$usuario = \App\Models\User::with(['roles', 'unidadesResponsables'])->where('usuario', 'like', '%gilberto.gomez%')->first();

if (!$usuario) {
    echo "No se encontró ningún usuario con nombre 'gilberto.gomez'\n";
    exit;
}

echo "Usuario encontrado: {$usuario->usuario} ({$usuario->correo})\n";
echo "Roles: " . implode(', ', $usuario->roles->pluck('name')->toArray()) . "\n";

$urgs = $usuario->unidadesResponsables;
if ($urgs->isEmpty()) {
    echo "No tiene Unidades Responsables de Gasto (URG) asignadas.\n";
} else {
    echo "URGs asignadas:\n";
    foreach ($urgs as $urg) {
        echo " - {$urg->numero}: {$urg->nombre}\n";
        
        // Find projects for this URG
        $proyectos = DB::connection('poa_prod')->table('proyectos as py')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->select('py.numero', 'py.nombre', 'py.ejercicio_id')
            ->where('ro.unidad_responsable_gasto_id', $urg->unidad_responsable_gasto_id)
            ->get();
            
        if ($proyectos->isEmpty()) {
            echo "   (No hay proyectos asociados a esta URG)\n";
        } else {
            echo "   Proyectos en esta URG:\n";
            foreach ($proyectos as $py) {
                // Determine exercise year
                $ejercicio = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio_id', $py->ejercicio_id)->value('ejercicio');
                echo "     * Proyecto {$py->numero} ({$ejercicio}): {$py->nombre}\n";
            }
        }
    }
}
