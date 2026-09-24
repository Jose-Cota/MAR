<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$ejercicio_id = '2027';
$area_id = '6';

$ejercicioRow    = DB::table('ejercicios')->where('ejercicio', $ejercicio_id)->first();
$ejercicio_db_id = $ejercicioRow ? $ejercicioRow->ejercicio_id : $ejercicio_id;
echo "Ejercicio DB ID: " . $ejercicio_db_id . "\n";

$urgSeleccionada = ($area_id !== 'todas')
    ? DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', $area_id)->first()
    : null;

$urgEstructuralMin = 240;
$rgIds = null;

$normalizar = function ($s) {
    $s = mb_strtolower(trim($s));
    $s = preg_replace('/[áàäâ]/u', 'a', $s);
    $s = preg_replace('/[éèëê]/u', 'e', $s);
    $s = preg_replace('/[íìïî]/u', 'i', $s);
    $s = preg_replace('/[óòöô]/u', 'o', $s);
    $s = preg_replace('/[úùüû]/u', 'u', $s);
    return $s;
};

if ($urgSeleccionada && $area_id !== 'todas') {
    $urgNombreClean = $normalizar($urgSeleccionada->nombre);
    echo "URG Name Clean: " . $urgNombreClean . "\n";
    
    if ($urgSeleccionada->unidad_responsable_gasto_id >= $urgEstructuralMin) {
        // Not hit
    } else {
        $urgEstructural = DB::table('unidades_responsables_gastos')
            ->where('ejercicio_id', $ejercicio_db_id)
            ->where('unidad_responsable_gasto_id', '>=', $urgEstructuralMin)
            ->get()
            ->first(function ($u) use ($normalizar, $urgNombreClean) {
                return $normalizar($u->nombre) === $urgNombreClean;
            });
            
        if ($urgEstructural) {
            echo "Found URG Estructural: " . $urgEstructural->nombre . "\n";
        } else {
            echo "NOT Found URG Estructural, using RO fallback\n";
            $roIdsPoa = DB::table('responsables_operativos')
                ->get()
                ->filter(function ($ro) use ($normalizar, $urgNombreClean) {
                    $roNorm = $normalizar($ro->nombre);
                    if ($roNorm === $urgNombreClean) return true;
                    
                    $urgCore = str_replace(['direccion de ', 'direccion general ', 'unidad de ', 'coordinacion de ', 'la '], '', $urgNombreClean);
                    $urgCore = str_replace('juridica', 'juridic', $urgCore);
                    $urgCore = str_replace('secretaria administrativa', 'administrativo', $urgCore);
                    
                    return strlen($urgCore) > 5 && str_contains($roNorm, $urgCore);
                })
                ->pluck('responsable_operativo_id')
                ->toArray();
            print_r($roIdsPoa);
            $rgIds = array_unique($roIdsPoa);
        }
    }
}
