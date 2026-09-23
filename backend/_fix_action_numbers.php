<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$jsonStr = file_get_contents('C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json');
$data = json_decode($jsonStr, true);

$actions = $data['poaActions'] ?? [];

$updated = 0;
foreach ($actions as $a) {
    if ($a['exercise'] == 2026) {
        $number = $a['number'];
        $text = $a['text'];
        
        $affected = DB::table('acciones_sustantivas')
            ->join('proyectos', 'proyectos.proyecto_id', '=', 'acciones_sustantivas.proyecto_id')
            ->where('proyectos.ejercicio_id', 17)
            ->where('acciones_sustantivas.descripcion', $text)
            ->update(['acciones_sustantivas.numero' => $number]);
            
        $updated += $affected;
    }
}

echo "Updated $updated actions with correct numbers!\n";
