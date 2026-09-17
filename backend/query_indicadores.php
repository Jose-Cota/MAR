<?php
use Illuminate\Support\Facades\DB;

$results = DB::connection('poa_prod')
    ->table('indicadores')
    ->where('proyecto_id', 28)
    // ->where('ur_id', 11) // I need to see the table structure to know if ur_id is in indicadores or proyectos
    ->get();

echo "RESULTADOS INDICADORES PROY 28:\n";
foreach($results as $r) {
    echo "ID: " . $r->indicador_id . "\n";
    echo "Nombre: " . $r->nombre . "\n";
    echo "Definicion/Objetivo: " . $r->definicion . "\n";
    echo "------------------\n";
}

$proyectos = DB::connection('poa_prod')
    ->table('proyectos')
    ->where('id', 28)
    ->get();

echo "PROYECTO 28:\n";
foreach($proyectos as $p) {
    print_r($p);
}
