<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    $risks27 = DB::table('riesgos')->where('ejercicio_id', 19)->get();
    $updatedCount = 0;
    
    foreach ($risks27 as $r) {
        if (empty($r->factores_internos) && !empty($r->factores)) {
            DB::table('riesgos')->where('id', $r->id)->update([
                'factores_internos' => $r->factores
            ]);
            $updatedCount++;
        }
    }
    
    DB::commit();
    echo "SUCCESS: Updated $updatedCount risks with factores_internos!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
