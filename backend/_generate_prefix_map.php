<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$seed = json_decode(file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json'), true);

$urgs = DB::table('urgs')->get();
$prefixToAreaId = [];

foreach ($seed['areas'] as $area) {
    $prefix = $area['id'];
    $name = mb_strtolower(trim($area['name']));
    
    $matchedUrgId = 1;
    $maxCoincidencias = 0;
    
    // Normalize string function
    $normalize = function($str) {
        $str = mb_strtolower(trim($str));
        $str = preg_replace('/[áàäâ]/u', 'a', $str);
        $str = preg_replace('/[éèëê]/u', 'e', $str);
        $str = preg_replace('/[íìïî]/u', 'i', $str);
        $str = preg_replace('/[óòöô]/u', 'o', $str);
        $str = preg_replace('/[úùüû]/u', 'u', $str);
        $str = preg_replace('/[^\p{L}\p{N} ]/u', ' ', $str);
        return $str;
    };
    
    $nameNorm = $normalize($name);
    $words = array_filter(explode(' ', $nameNorm), fn($w) => mb_strlen($w) > 3);
    
    foreach ($urgs as $urg) {
        $urgNameNorm = $normalize($urg->urg);
        $coincidencias = 0;
        foreach ($words as $w) {
            if (strpos($urgNameNorm, $w) !== false) {
                $coincidencias++;
            }
        }
        if ($coincidencias > $maxCoincidencias) {
            $maxCoincidencias = $coincidencias;
            $matchedUrgId = $urg->id;
        }
    }
    
    // Exact mapping for magistraturas using initials
    if ($prefix === 'PAAH') $matchedUrgId = 21;
    if ($prefix === 'PJHR') $matchedUrgId = 22;
    if ($prefix === 'POVR') $matchedUrgId = 23;
    if ($prefix === 'PKSL') $matchedUrgId = 24;
    if ($prefix === 'PLPJC') $matchedUrgId = 25;
    
    if ($prefix === 'CI') $matchedUrgId = 4; // Contraloría Interna -> OIC
    
    $prefixToAreaId[$prefix] = $matchedUrgId;
}

echo var_export($prefixToAreaId, true) . "\n";
