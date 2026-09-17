<?php
use Illuminate\Support\Facades\DB;

$proyecto_2027 = 1438;
echo "=== METAS 2027 ===\n";
echo json_encode(DB::select("SELECT * FROM metas WHERE proyecto_id = ?", [$proyecto_2027]), JSON_PRETTY_PRINT);

echo "\n=== INDICADORES 2027 ===\n";
echo json_encode(DB::select("SELECT * FROM indicadores WHERE proyecto_id = ?", [$proyecto_2027]), JSON_PRETTY_PRINT);
exit;
