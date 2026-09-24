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

// Test for Recursos Materiales
$urgSeleccionada = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('nombre', 'like', '%Materiales%')->first();
$urgNombreClean = $normalizar($urgSeleccionada->nombre);

$roIdsPoa = DB::connection('poa_prod')->table('responsables_operativos')
    ->where('ejercicio_id', 19)
    ->get()
    ->filter(function ($ro) use ($normalizar, $urgNombreClean) {
        $roNorm = $normalizar($ro->nombre);
        if ($roNorm === $urgNombreClean) return true;
        
        $urgCore = str_replace(['direccion de ', 'direccion general ', 'unidad de ', 'coordinacion de ', 'la '], '', $urgNombreClean);
        $urgCore = str_replace('juridica', 'juridic', $urgCore);
        $urgCore = str_replace('secretaria administrativa', 'administrativo', $urgCore);
        
        echo "   Testing RO: $roNorm against Core: $urgCore\n";
        
        return strlen($urgCore) > 5 && str_contains($roNorm, $urgCore);
    })
    ->pluck('responsable_operativo_id')
    ->toArray();

print_r($roIdsPoa);
