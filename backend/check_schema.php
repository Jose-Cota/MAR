<?php
use Illuminate\Support\Facades\DB;

$proyectos = DB::select("DESCRIBE proyectos");
$metas = DB::select("DESCRIBE metas");
$ejercicios = DB::select("SELECT * FROM ejercicios");

echo "=== PROYECTOS ===\n";
echo json_encode($proyectos, JSON_PRETTY_PRINT);
echo "\n=== METAS ===\n";
echo json_encode($metas, JSON_PRETTY_PRINT);
echo "\n=== EJERCICIOS ===\n";
echo json_encode($ejercicios, JSON_PRETTY_PRINT);
exit;
