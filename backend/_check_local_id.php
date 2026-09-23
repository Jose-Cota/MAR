<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$risks26 = DB::table('riesgos')->where('ejercicio_id', 19)->select('local_id')->limit(5)->get();
$risks27 = DB::table('riesgos')->where('ejercicio_id', 19)->select('local_id')->limit(5)->get();

print_r(\);
$mismatchCount = 0;
foreach ($risks27 as $r27) {
    $r26 = collect($risks26)->where('local_id', $r27->local_id)->where('area_id', '!=', $r27->area_id)->first();
    // actually, we should match by something else because local_id repeats across areas (R1, R2, etc)
    // we should just group by area_id and count
}

$counts26 = DB::table('riesgos')->where('ejercicio_id', 19)->select('local_id')->limit(5)->get();
$counts27 = DB::table('riesgos')->where('ejercicio_id', 19)->select('local_id')->limit(5)->get();

print_r(\);
print_r($counts26->toArray());

print_r(\);
print_r($counts27->toArray());
