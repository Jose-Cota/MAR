<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$urg7 = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', 7)->first();
echo "URG 7: " . $urg7->nombre . "\n";

$mar7 = DB::table('0201sadpyrf_mar2026.areas')->where('area_id', 7)->first();
echo "MAR 7: " . $mar7->nombre . "\n";

$urg15 = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', 15)->first();
echo "URG 15: " . $urg15->nombre . "\n";

$mar19 = DB::table('0201sadpyrf_mar2026.areas')->where('area_id', 19)->first();
echo "MAR 19: " . $mar19->nombre . "\n";

$riesgos15 = DB::table('0201sadpyrf_mar2026.riesgos')->where('area_id', 15)->get();
echo "Riesgos area_id 15: " . count($riesgos15) . " - " . ($riesgos15->count() > 0 ? $riesgos15[0]->objetivo : "") . "\n";

$riesgos17 = DB::table('0201sadpyrf_mar2026.riesgos')->where('area_id', 17)->get();
echo "Riesgos area_id 17: " . count($riesgos17) . " - " . ($riesgos17->count() > 0 ? $riesgos17[0]->objetivo : "") . "\n";
