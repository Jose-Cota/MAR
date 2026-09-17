<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::statement('DROP TABLE IF EXISTS riesgo_indicadores');
DB::statement('DROP TABLE IF EXISTS riesgo_controles');
DB::statement('DROP TABLE IF EXISTS actividad_riesgo');
DB::statement('DROP TABLE IF EXISTS riesgos');
DB::table('migrations')->where('migration', '2026_09_17_000000_create_mar_tables')->delete();

echo "Dropped partially created MAR tables.\n";
