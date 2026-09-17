<?php

$duplicates = DB::connection('poa_prod')->select('
    SELECT meta_id, COUNT(*) as count 
    FROM indicadores 
    GROUP BY meta_id 
    HAVING count > 1
');

echo "Found " . count($duplicates) . " metas with duplicates.\n";

foreach ($duplicates as $dup) {
    if ($dup->meta_id == 0) continue; // Ignore meta_id = 0
    echo "Processing meta_id: " . $dup->meta_id . "\n";
    $indicadores = DB::connection('poa_prod')->table('indicadores')
        ->where('meta_id', $dup->meta_id)
        ->orderBy('indicador_id', 'desc')
        ->get();
    
    // Keep the first one (most recently created, or highest ID)
    $keepId = $indicadores->first()->indicador_id;
    echo "Keeping indicator_id: " . $keepId . "\n";
    
    foreach ($indicadores as $ind) {
        if ($ind->indicador_id != $keepId) {
            echo "Deleting indicator_id: " . $ind->indicador_id . "\n";
            DB::connection('poa_prod')->table('indicadores')
                ->where('indicador_id', $ind->indicador_id)
                ->delete();
        }
    }
}
echo "Done.\n";
