<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    $ej2027 = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', 2027)->first();
    $ejId = $ej2027->ejercicio_id;
    
    $pys = DB::connection('poa_prod')->table('proyectos')
        ->join('responsables_operativos as ro', 'proyectos.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
        ->join('subprogramas as sp', 'proyectos.subprograma_id', '=', 'sp.subprograma_id')
        ->join('programas as p', 'sp.programa_id', '=', 'p.programa_id')
        ->where('urg.ejercicio_id', $ejId)
        ->select('proyectos.proyecto_id', 'proyectos.nombre', 'proyectos.numero as py_num', 'sp.numero as sp_num', 'p.numero as pg_num', 'urg.numero as urg_num', 'ro.numero as ro_num')
        ->get();
        
    $withMetas = 0;
    $withoutMetas = [];
    
    foreach($pys as $p) {
        $m = DB::connection('poa_prod')->table('metas')->where('proyecto_id', $p->proyecto_id)->count();
        if($m > 0) {
            $withMetas++;
        } else {
            $withoutMetas[] = $p;
        }
        
        // Check for 01.01.01.01.01
        $clave = $p->pg_num . '.' . $p->sp_num . '.' . $p->urg_num . '.' . $p->ro_num . '.' . $p->py_num;
        if ($clave == '01.01.01.01.01' || $p->py_num == '01.01.01.01.01') {
            echo "Found matching project via clave: {$clave} -> ID: {$p->proyecto_id} | {$p->nombre}\n";
        }
    }
    
    echo "--- METAS 2027 ---\n";
    echo 'Total Proyectos 2027: ' . count($pys) . "\n";
    echo 'Con metas: ' . $withMetas . "\n";
    echo 'Sin metas: ' . count($withoutMetas) . "\n";
    if(count($withoutMetas) > 0) {
        echo "Ejemplos de proyectos sin metas:\n";
        foreach (array_slice($withoutMetas, 0, 5) as $wm) {
            echo "- ID " . $wm->proyecto_id . ": " . $wm->nombre . "\n";
        }
    }
    
    echo "\n--- FICHA DESCRIPTIVA 01.01.01.01.01 ---\n";
    if (Schema::connection('poa_prod')->hasTable('fichas_descriptivas')) {
        $fichas = DB::connection('poa_prod')->table('fichas_descriptivas')->where('clave', 'like', '%01.01.01.01.01%')->get();
        if(count($fichas) > 0) {
            echo "Found in fichas_descriptivas:\n";
            print_r($fichas);
        } else {
            echo "No se encontró clave 01.01.01.01.01 en tabla fichas_descriptivas.\n";
        }
    } else {
        echo "Tabla fichas_descriptivas no existe.\n";
        
        // Maybe it's called fichas or similar?
        $tables = DB::connection('poa_prod')->select('SHOW TABLES');
        $found = false;
        foreach ($tables as $t) {
            $tableName = (array)$t;
            $tableName = array_values($tableName)[0];
            if (strpos($tableName, 'ficha') !== false) {
                echo "Encontré tabla: $tableName\n";
                $res = DB::connection('poa_prod')->table($tableName)->limit(1)->get();
                print_r($res);
                $found = true;
            }
        }
        if (!$found) echo "No hay tablas con 'ficha' en el nombre.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
