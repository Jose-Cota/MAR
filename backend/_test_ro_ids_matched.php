<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$normalizar = function ($s) {
    $s = mb_strtolower(trim($s));
    $s = preg_replace('/[áàäâ]/u', 'a', $s);
    $s = preg_replace('/[éèëê]/u', 'e', $s);
    $s = preg_replace('/[íìïî]/u', 'i', $s);
    $s = preg_replace('/[óòöô]/u', 'o', $s);
    $s = preg_replace('/[úùüû]/u', 'u', $s);
    $s = preg_replace('/[^a-z0-9 ]/u', '', $s);
    return trim($s);
};

$urgNombreClean = $normalizar("Ponencia de la Magistrada Laura Patricia Jiménez Castillo");

$roIdsPoa = DB::table('responsables_operativos')
    ->get()
    ->filter(function ($ro) use ($normalizar, $urgNombreClean) {
        $roNorm = $normalizar($ro->nombre);
        return $roNorm === $urgNombreClean || stripos($roNorm, $urgNombreClean) !== false || stripos($urgNombreClean, $roNorm) !== false;
    })
    ->pluck('responsable_operativo_id')
    ->toArray();

echo "RO IDs: " . implode(', ', $roIdsPoa) . "\n";
