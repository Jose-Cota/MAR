<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$c = DB::table('riesgos')->count();
echo "Total risks in DB: $c\n";

$c1819 = DB::table('riesgos')->whereIn('ejercicio_id', [18, 19])->count();
echo "Risks with 18 or 19: $c1819\n";

$c2627 = DB::table('riesgos')->whereIn('ejercicio_id', [2026, 2027])->count();
echo "Risks with 2026 or 2027: $c2627\n";

$counts = DB::table('riesgos')->select('ejercicio_id', DB::raw('count(*) as total'))->groupBy('ejercicio_id')->get();
print_r($counts);
