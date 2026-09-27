<?php
use Illuminate\Support\Facades\DB;

$ej = DB::table('ejercicios')->get();
echo "Ejercicios:\n";
foreach ($ej as $e) {
    echo json_encode($e) . "\n";
}

$ur = DB::table('unidades_responsables_gastos')->where('nombre', 'like', '%Informáticos%')->first();
echo "UR:\n" . json_encode($ur) . "\n";

$riesgos = DB::table('riesgos')->where('area_id', $ur->unidad_responsable_gasto_id)->get();
echo "Riesgos:\n";
foreach ($riesgos as $r) {
    echo "ID: $r->id, Ejercicio ID: $r->ejercicio_id\n";
}
