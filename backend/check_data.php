<?php
use Illuminate\Support\Facades\DB;

$proyecto_2026 = 884;
$proyecto_2027 = 1438;

echo "=== METAS 2026 ===\n";
echo json_encode(DB::select("SELECT * FROM metas WHERE proyecto_id = ?", [$proyecto_2026]), JSON_PRETTY_PRINT);

echo "\n=== INDICADORES 2026 ===\n";
echo json_encode(DB::select("SELECT * FROM indicadores WHERE proyecto_id = ?", [$proyecto_2026]), JSON_PRETTY_PRINT);

echo "\n=== ACTIVIDADES 2026 ===\n";
echo json_encode(DB::select("SELECT * FROM actividades_sustantivas WHERE proyecto_id = ?", [$proyecto_2026]), JSON_PRETTY_PRINT);

echo "\n=== METAS 2027 ===\n";
echo json_encode(DB::select("SELECT * FROM metas WHERE proyecto_id = ?", [$proyecto_2027]), JSON_PRETTY_PRINT);

exit;
