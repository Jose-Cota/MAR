<?php
use Illuminate\Support\Facades\DB;

// Find the project in 2026 and 2027
$proyectos = DB::select("
    SELECT *
    FROM proyectos p
    WHERE p.ejercicio_id IN (17, 19) 
      AND p.nombre LIKE '%Control de Gesti%n Jurisdiccional%'
");

echo "=== PROYECTOS ENCONTRADOS ===\n";
echo json_encode($proyectos, JSON_PRETTY_PRINT);

foreach ($proyectos as $p) {
    echo "\n=== METAS PARA PROYECTO ID {$p->proyecto_id} (Ejercicio: {$p->ejercicio_id}) ===\n";
    $metas = DB::select("SELECT meta_id, nombre, tipo FROM metas WHERE proyecto_id = ?", [$p->proyecto_id]);
    echo json_encode($metas, JSON_PRETTY_PRINT);
}

exit;
