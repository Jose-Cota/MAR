<?php
use Illuminate\Support\Facades\DB;

echo "=== ACTIVIDADES SUSTANTIVAS SCHEMA ===\n";
echo json_encode(DB::select("DESCRIBE actividades_sustantivas"), JSON_PRETTY_PRINT);

echo "\n=== INDICADORES SCHEMA ===\n";
echo json_encode(DB::select("DESCRIBE indicadores"), JSON_PRETTY_PRINT);
exit;
