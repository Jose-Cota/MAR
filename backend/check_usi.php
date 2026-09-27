<?php
use Illuminate\Support\Facades\DB;

$areas = DB::table('unidades_responsables')->where('nombre', 'like', '%Informáticos%')
        ->orWhere('denominacion', 'like', '%Informáticos%')
        ->get();
foreach ($areas as $a) {
    echo "ID: " . ($a->unidad_responsable_gasto_id ?? $a->id_unidad ?? $a->id ?? 'N/A') . " - " . ($a->nombre ?? $a->denominacion) . "\n";
}
