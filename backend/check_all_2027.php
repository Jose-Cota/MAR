<?php
use Illuminate\Support\Facades\DB;

$proyectos = DB::select("
    SELECT p.proyecto_id, p.numero as clave_proyecto, p.nombre, p.ejercicio_id, ro.numero as clave_ro, urg.numero as clave_urg
    FROM proyectos p
    LEFT JOIN responsables_operativos ro ON p.responsable_operativo_id = ro.responsable_operativo_id
    LEFT JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
    WHERE p.ejercicio_id = 19
");

$incompletos = [];

foreach ($proyectos as $p) {
    $principales = DB::table('metas')->where('proyecto_id', $p->proyecto_id)->where('tipo', 'principal')->count();
    $complementarias = DB::table('metas')->where('proyecto_id', $p->proyecto_id)->where('tipo', 'complementaria')->count();
    $indicadores = DB::table('indicadores')->where('proyecto_id', $p->proyecto_id)->count();
    
    if ($principales == 0 || $complementarias == 0 || $indicadores == 0) {
        $incompletos[] = [
            'id' => $p->proyecto_id,
            'clave' => $p->clave_urg . '.' . $p->clave_ro . '... .' . $p->clave_proyecto, // Simplified clave just for reference
            'nombre' => $p->nombre,
            'principales' => $principales,
            'complementarias' => $complementarias,
            'indicadores' => $indicadores
        ];
    }
}

echo json_encode($incompletos, JSON_PRETTY_PRINT);
exit;
