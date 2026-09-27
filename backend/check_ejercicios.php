<?php
use Illuminate\Support\Facades\DB;

$ejercicios = DB::table('ejercicios')->get();
foreach ($ejercicios as $e) {
    echo "Ejercicio ID: {$e->id}, Año: {$e->numero}\n";
}

$r = DB::table('riesgos')->first();
if ($r) {
    echo json_encode(array_keys((array)$r)) . "\n";
}
