<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$normalizar = function ($s) {
    $s = mb_strtolower(trim($s));
    $s = preg_replace('/[áàäâ]/u', 'a', $s);
    $s = preg_replace('/[éèëê]/u', 'e', $s);
    $s = preg_replace('/[íìïî]/u', 'i', $s);
    $s = preg_replace('/[óòöô]/u', 'o', $s);
    $s = preg_replace('/[úùüû]/u', 'u', $s);
    return $s;
};

$urgSeleccionada = DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', 6)->first();
$urgNombreClean = $normalizar($urgSeleccionada->nombre);
echo "URG Clean: " . $urgNombreClean . "\n";

$roIdsPoa = DB::table('responsables_operativos')
    ->get()
    ->filter(function ($ro) use ($normalizar, $urgNombreClean) {
        $roNorm = $normalizar($ro->nombre);
        
        $urgCore = str_replace(['direccion de ', 'direccion general ', 'unidad de ', 'coordinacion de ', 'la '], '', $urgNombreClean);
        $urgCore = str_replace('juridica', 'juridic', $urgCore);
        $urgCore = str_replace('secretaria administrativa', 'administrativo', $urgCore);
        
        if (strlen($urgCore) > 5 && str_contains($roNorm, $urgCore)) {
            echo "Match found: " . $roNorm . " matches core " . $urgCore . "\n";
            return true;
        }
        return false;
    })
    ->pluck('responsable_operativo_id')
    ->toArray();

print_r($roIdsPoa);

if (empty($roIdsPoa)) {
    echo "NO IDS FOUND!\n";
} else {
    $query = DB::table('proyectos')
        ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
        ->where('proyectos.ejercicio_id', 19)
        ->whereIn('proyectos.responsable_operativo_id', $roIdsPoa)
        ->get();
    echo "Proyectos found: " . count($query) . "\n";
}
