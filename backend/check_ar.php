<?php
use Illuminate\Support\Facades\DB;

$ar = DB::table('actividad_riesgo')->first();
if ($ar) {
    echo json_encode(array_keys((array)$ar)) . "\n";
} else {
    echo "actividad_riesgo is empty.\n";
}
