<?php
use Illuminate\Support\Facades\DB;

$meta_2026 = 4021;
$meta_2027 = 7150;

echo "=== INDICADORES 2026 (meta_id 4021) ===\n";
echo json_encode(DB::select("SELECT * FROM indicadores WHERE meta_id = ?", [$meta_2026]), JSON_PRETTY_PRINT);

echo "\n=== ACTIVIDADES 2026 (indicador_id 7179) ===\n";
echo json_encode(DB::select("SELECT * FROM actividades_sustantivas WHERE indicador_id = 7179"), JSON_PRETTY_PRINT);

echo "\n=== INDICADORES 2027 (meta_id 7150) ===\n";
echo json_encode(DB::select("SELECT * FROM indicadores WHERE meta_id = ?", [$meta_2027]), JSON_PRETTY_PRINT);

// For 2027, first get indicators, then get activities
$inds_2027 = DB::select("SELECT indicador_id FROM indicadores WHERE meta_id = ?", [$meta_2027]);
if (count($inds_2027) > 0) {
    echo "\n=== ACTIVIDADES 2027 ===\n";
    foreach($inds_2027 as $ind) {
        echo json_encode(DB::select("SELECT * FROM actividades_sustantivas WHERE indicador_id = ?", [$ind->indicador_id]), JSON_PRETTY_PRINT);
    }
}

exit;
