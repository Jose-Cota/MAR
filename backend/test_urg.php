<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$user = User::where('usuario', 'jose.cota')->first();
echo "User ID: {$user->id}\n";
echo "User POA_ID: {$user->usuario_poa_id}\n";
echo "User Area ID: {$user->area_id}\n";

$urgIds = DB::table('usuario_unidad_responsable')
    ->where('usuario_poa_id', $user->id)
    ->where('unidad_responsable_gasto_id', '>', 0)
    ->pluck('unidad_responsable_gasto_id')
    ->toArray();

echo "URG IDs: " . json_encode($urgIds) . "\n";

$userUrgNumbers = [];
if (!empty($urgIds)) {
    $userUrgNumbers = DB::table('unidades_responsables_gastos')
        ->whereIn('unidad_responsable_gasto_id', $urgIds)
        ->pluck('numero')
        ->toArray();
}
if (empty($userUrgNumbers) && $user->area_id) {
    $userUrgNumbers = DB::table('unidades_responsables_gastos')
        ->where('unidad_responsable_gasto_id', $user->area_id)
        ->pluck('numero')
        ->toArray();
}

echo "URG Numbers: " . json_encode($userUrgNumbers) . "\n";
