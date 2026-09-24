<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', 21)->first();
$rosEjercicio = DB::table('responsables_operativos')->where('ejercicio_id', 19)->get()->groupBy('unidad_responsable_gasto_id');
$urgNombreClean = preg_replace('/[áàäâ]/u', 'a', mb_strtolower(trim($u->nombre)));
$urgNombreClean = preg_replace('/[éèëê]/u', 'e', $urgNombreClean);
$urgNombreClean = preg_replace('/[íìïî]/u', 'i', $urgNombreClean);
$urgNombreClean = preg_replace('/[óòöô]/u', 'o', $urgNombreClean);
$urgNombreClean = preg_replace('/[úùüû]/u', 'u', $urgNombreClean);
$palabras = array_filter(explode(' ', preg_replace('/[^\p{L}\p{N} ]/u', ' ', $urgNombreClean)), fn($p) => mb_strlen($p) > 5);
$maxCoincidencias = 0;
$roIdsPoa = [];
foreach ($rosEjercicio as $urgIdPoa => $rosGrupo) {
    foreach ($rosGrupo as $ro) {
        $rClean = mb_strtolower(trim($ro->nombre));
        $coincidencias = 0;
        foreach ($palabras as $palabra) {
            if (str_contains($rClean, $palabra)) $coincidencias++;
        }
        if ($coincidencias > 0) {
            if ($coincidencias > $maxCoincidencias) {
                $maxCoincidencias = $coincidencias;
                $roIdsPoa = [$ro->responsable_operativo_id];
            } elseif ($coincidencias == $maxCoincidencias) {
                $roIdsPoa[] = $ro->responsable_operativo_id;
            }
        }
    }
}
echo json_encode(['palabras' => array_values($palabras), 'roIds' => $roIdsPoa]);
