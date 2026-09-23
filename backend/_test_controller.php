<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$urgSeleccionada = DB::table('unidades_responsables_gastos')->where('nombre', 'Secretaría Administrativa')->first();
$ejercicio_db_id = 17;

$rosEjercicio = DB::table('responsables_operativos')
    ->where('ejercicio_id', $ejercicio_db_id)
    ->get()
    ->groupBy('unidad_responsable_gasto_id');

$urgNombreLower = mb_strtolower(trim($urgSeleccionada->nombre));
$urgNumero      = trim($urgSeleccionada->numero);

$urgIdsPoa = [];
foreach ($rosEjercicio as $urgIdPoa => $rosGrupo) {
    foreach ($rosGrupo as $ro) {
        $roNombreClean = preg_replace('/[áàäâ]/u', 'a', mb_strtolower(trim($ro->nombre)));
        $roNombreClean = preg_replace('/[éèëê]/u', 'e', $roNombreClean);
        $roNombreClean = preg_replace('/[íìïî]/u', 'i', $roNombreClean);
        $roNombreClean = preg_replace('/[óòöô]/u', 'o', $roNombreClean);
        $roNombreClean = preg_replace('/[úùüû]/u', 'u', $roNombreClean);
        
        $roNombreClean = str_replace(['director', 'directora'], 'direccion', $roNombreClean);
        $roNombreClean = str_replace(['presidente', 'presidenta'], 'presidencia', $roNombreClean);
        $roNombreClean = str_replace(['secretario', 'secretaria'], 'secretaria', $roNombreClean);
        $roNombreClean = str_replace(['contralor', 'contralora'], 'contraloria', $roNombreClean);
        $roNombreClean = str_replace(['interno', 'interna'], 'interna', $roNombreClean);
        $roNombreClean = str_replace(['defensor', 'defensora'], 'defensoria', $roNombreClean);
        $roNombreClean = str_replace(['ciudadano', 'ciudadana'], 'ciudadana', $roNombreClean);
        $roNombreClean = str_replace(['administrativo', 'administrativos'], 'administrativa', $roNombreClean);
        $roNombreClean = str_replace(['tecnico', 'tecnica'], 'tecnica', $roNombreClean);
        
        $urgNombreClean = preg_replace('/[áàäâ]/u', 'a', mb_strtolower(trim($urgSeleccionada->nombre)));
        $urgNombreClean = preg_replace('/[éèëê]/u', 'e', $urgNombreClean);
        $urgNombreClean = preg_replace('/[íìïî]/u', 'i', $urgNombreClean);
        $urgNombreClean = preg_replace('/[óòöô]/u', 'o', $urgNombreClean);
        $urgNombreClean = preg_replace('/[úùüû]/u', 'u', $urgNombreClean);

        $palabras = array_filter(
            explode(' ', preg_replace('/[^\p{L}\p{N} ]/u', ' ', $urgNombreClean)),
            fn($p) => mb_strlen($p) > 5
        );
        $coincidencias = 0;
        foreach ($palabras as $palabra) {
            if (str_contains($roNombreClean, $palabra)) {
                $coincidencias++;
            }
        }
        if ($coincidencias >= 2 || (count($palabras) === 1 && $coincidencias >= 1)) {
            $urgIdsPoa[] = (int) $urgIdPoa;
            break;
        }
    }
}

$rgIds = array_unique($urgIdsPoa);

$query = DB::table('proyectos')
    ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
    ->where('proyectos.ejercicio_id', $ejercicio_db_id)
    ->select(
        'proyectos.*',
        'proyectos.proyecto_id as id',
        'responsables_operativos.unidad_responsable_gasto_id as urg_id',
        'responsables_operativos.nombre as ro_nombre'
    );

if ($rgIds !== null) {
    if (empty($rgIds)) {
        echo "EMPTY rgIds!\n";
    } else {
        $query->whereIn('responsables_operativos.unidad_responsable_gasto_id', $rgIds);
    }
}

$proyectos = $query->get();
foreach ($proyectos as $p) {
    echo "Found PROJ: {$p->nombre}\n";
}
