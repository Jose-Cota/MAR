<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Check if tabla usuario_unidad_responsable exists and its contents
try {
    $count = DB::table('usuario_unidad_responsable')->count();
    echo "usuario_unidad_responsable count: $count\n";
    
    $sample = DB::table('usuario_unidad_responsable')->take(5)->get();
    foreach ($sample as $r) {
        echo "  " . json_encode($r) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Check user jose.cota area_id
$user = DB::table('usuarios')->where('usuario', 'jose.cota')->first();
echo "\njose.cota: " . json_encode($user) . "\n";

// Check user Raymundo Aparicio
$user2 = DB::table('usuarios')->where('nombre', 'like', '%Raymundo%')->first();
echo "\nRaymundo: " . json_encode($user2) . "\n";
