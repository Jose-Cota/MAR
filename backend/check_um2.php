<?php
use Illuminate\Support\Facades\DB;

$um_2026 = DB::table('unidades_medidas')->where('unidad_medida_id', 2470)->first();
echo "UM 2026 for Meta Complementaria: " . json_encode($um_2026) . "\n";

$ums_2027 = DB::table('unidades_medidas')->where('ejercicio_id', 19)->where('nombre', $um_2026->nombre)->first();
echo "UM 2027 by nombre: " . json_encode($ums_2027) . "\n";

$um_ind_2026 = DB::table('unidades_medidas')->where('unidad_medida_id', 2323)->first();
echo "UM 2026 for Indicador: " . json_encode($um_ind_2026) . "\n";

$um_ind_2027 = DB::table('unidades_medidas')->where('ejercicio_id', 19)->where('nombre', $um_ind_2026->nombre)->first();
echo "UM 2027 for Indicador by nombre: " . json_encode($um_ind_2027) . "\n";
exit;
