<?php
use Illuminate\Support\Facades\DB;

$proyectos = DB::table('proyectos')->where('ejercicio_id', 19)->get();
echo "Total proyectos en 2027: " . count($proyectos) . "\n";

$incompletos = 0;
foreach ($proyectos as $p) {
    $principales = DB::table('metas')->where('proyecto_id', $p->proyecto_id)->where('tipo', 'principal')->count();
    $complementarias = DB::table('metas')->where('proyecto_id', $p->proyecto_id)->where('tipo', 'complementaria')->count();
    $indicadores = DB::table('indicadores')->where('proyecto_id', $p->proyecto_id)->count();
    
    if ($principales == 0 || $complementarias == 0 || $indicadores == 0) {
        $incompletos++;
        echo "- ID {$p->proyecto_id} | Princ: $principales | Comp: $complementarias | Ind: $indicadores\n";
    }
}
echo "Total incompletos: $incompletos\n";
exit;
