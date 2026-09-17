<?php
use Illuminate\Support\Facades\DB;

$proyectos = DB::select("
    SELECT p.proyecto_id
    FROM proyectos p
    WHERE p.ejercicio_id = 19
");

foreach ($proyectos as $p) {
    $comp = DB::table('metas')->where('proyecto_id', $p->proyecto_id)->where('tipo', 'complementaria')->count();
    if ($comp == 0) {
        echo "Proyecto sin meta complementaria: " . $p->proyecto_id . "\n";
    }
}
echo "Fin de revisión.\n";
exit;
