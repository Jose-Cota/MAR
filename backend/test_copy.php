<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$old_um = DB::connection('poa_prod')->select("SELECT * FROM 0201sadpyrf_poa.unidades_medidas WHERE unidad_medida_id = 1047");
print_r($old_um);

if (count($old_um) > 0) {
    // Check if it already exists
    $existing = DB::connection('poa_prod')->table('unidades_medidas')->where('unidad_medida_id', 1047)->first();
    if (!$existing) {
        $insertData = (array) $old_um[0];
        // Change ejercicio_id to 3 for 2027.
        // Wait, what is the ID for 2027?
        $ej = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2027)->first();
        if ($ej) {
            $insertData['ejercicio_id'] = $ej->ejercicio_id;
            DB::connection('poa_prod')->table('unidades_medidas')->insert($insertData);
            echo "Inserted 1047 successfully.\n";
        } else {
            echo "Ejercicio 2027 not found.\n";
        }
    } else {
        echo "Already exists in current DB.\n";
    }
} else {
    echo "Not found in old DB.\n";
}
