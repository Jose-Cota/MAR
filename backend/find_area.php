<?php
$areas = \App\Models\UnidadResponsable::where('nombre', 'like', '%Unidad de Servicios Informáticos%')
        ->orWhere('denominacion', 'like', '%Unidad de Servicios Informáticos%')
        ->get();
if ($areas->isEmpty()) {
    echo "Area not found.\n";
    exit;
}
$areaId = $areas->first()->unidad_responsable_gasto_id ?? $areas->first()->id_unidad ?? $areas->first()->id;
echo "Area ID: " . $areaId . "\n";
