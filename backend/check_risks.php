<?php
use Illuminate\Support\Facades\DB;

$r27 = DB::table('riesgos')->where('ejercicio_id', 2027)->get();
echo "Total risks for 2027: " . $r27->count() . "\n";
if ($r27->count() > 0) {
    $uniqueAreas = $r27->pluck('area_id')->unique();
    echo "Unique area_ids in 2027: " . implode(", ", $uniqueAreas->toArray()) . "\n";
}
