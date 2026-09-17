<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find the project that has exactly 2 principal metas and 3 complementary metas
$projects = DB::connection('poa_prod')->table('metas')
    ->select('proyecto_id', DB::raw('count(*) as total'))
    ->groupBy('proyecto_id')
    ->having('total', '>=', 5)
    ->orderBy('proyecto_id', 'desc')
    ->get();

foreach ($projects as $p) {
    $metas = DB::connection('poa_prod')->table('metas')
        ->where('proyecto_id', $p->proyecto_id)
        ->orderBy('tipo')
        ->orderBy('orden')
        ->orderBy('meta_id')
        ->get();
    
    $principals = $metas->where('tipo', 'principal')->values();
    $comps = $metas->where('tipo', '!=', 'principal')->values();
    
    if ($principals->count() >= 2 && $comps->count() >= 3) {
        $mp1 = $principals[0];
        $mp2 = $principals[1];
        
        $mc3 = $comps[0];
        $mc4 = $comps[1];
        $mc5 = $comps[2];
        
        // Link MC 3 and 4 to MP 1
        DB::connection('poa_prod')->table('metas')->where('meta_id', $mc3->meta_id)->update(['meta_padre_id' => $mp1->meta_id]);
        DB::connection('poa_prod')->table('metas')->where('meta_id', $mc4->meta_id)->update(['meta_padre_id' => $mp1->meta_id]);
        
        // Link MC 5 to MP 2
        DB::connection('poa_prod')->table('metas')->where('meta_id', $mc5->meta_id)->update(['meta_padre_id' => $mp2->meta_id]);
        
        echo "Updated project {$p->proyecto_id}\n";
        break;
    }
}
