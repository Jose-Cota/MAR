<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanIndicadores extends Command
{
    protected $signature = 'clean:indicadores';
    protected $description = 'Clean duplicate indicators from the database';

    public function handle()
    {
        $duplicates = DB::connection('poa_prod')->select('
            SELECT meta_id, COUNT(*) as count 
            FROM indicadores 
            GROUP BY meta_id 
            HAVING count > 1
        ');

        $this->info("Found " . count($duplicates) . " metas with duplicates.");

        foreach ($duplicates as $dup) {
            if ($dup->meta_id == 0) continue;
            
            $this->info("Processing meta_id: " . $dup->meta_id);
            
            $indicadores = DB::connection('poa_prod')->table('indicadores')
                ->where('meta_id', $dup->meta_id)
                ->orderBy('indicador_id', 'desc')
                ->get();
            
            $keepId = $indicadores->first()->indicador_id;
            $this->info("Keeping indicator_id: " . $keepId);
            
            foreach ($indicadores as $ind) {
                if ($ind->indicador_id != $keepId) {
                    $this->info("Deleting indicator_id: " . $ind->indicador_id);
                    DB::connection('poa_prod')->table('indicadores')
                        ->where('indicador_id', $ind->indicador_id)
                        ->delete();
                }
            }
        }
        
        $this->info("Done.");
    }
}
