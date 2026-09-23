<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$risks26 = DB::table('riesgos')->where('ejercicio_id', 17)->select('local_id', 'area_id')->get()->keyBy('local_id');
$risks27 = DB::table('riesgos')->where('ejercicio_id', 19)->select('local_id', 'area_id')->get();

echo "Mismatched area_ids between 2026 and 2027:\n";
$mismatchCount = 0;
foreach ($risks27 as $r27) {
    $r26 = collect($risks26)->where('local_id', $r27->local_id)->where('area_id', '!=', $r27->area_id)->first();
    // actually, we should match by something else because local_id repeats across areas (R1, R2, etc)
    // we should just group by area_id and count
}

$counts26 = DB::table('riesgos')->where('ejercicio_id', 17)->select('area_id', DB::raw('count(*) as count'))->groupBy('area_id')->pluck('count', 'area_id');
$counts27 = DB::table('riesgos')->where('ejercicio_id', 19)->select('area_id', DB::raw('count(*) as count'))->groupBy('area_id')->pluck('count', 'area_id');

echo "Area counts 2026:\n";
print_r($counts26->toArray());

echo "Area counts 2027:\n";
print_r($counts27->toArray());
