<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::table('riesgos')->where('ejercicio_id', 19)->delete();

$proyectos2026 = DB::table('proyectos')->where('ejercicio_id', 17)->get();
$proyectos2027 = DB::table('proyectos')->where('ejercicio_id', 19)->get();

$pMap = []; // 2026_pid => 2027_pid
foreach ($proyectos2026 as $p26) {
    // try exact name match after replacing year
    $expectedName2027 = str_replace('2026', '2027', $p26->nombre);
    $p27 = collect($proyectos2027)->first(function($val) use ($expectedName2027, $p26) {
        return mb_strtolower(trim($val->nombre)) === mb_strtolower(trim($expectedName2027)) ||
               mb_strtolower(trim($val->nombre)) === mb_strtolower(trim($p26->nombre));
    });
    
    if (!$p27) {
        // try matching by RO if there's only 1 project for that RO
        $p27s = collect($proyectos2027)->where('responsable_operativo_id', $p26->responsable_operativo_id);
        if ($p27s->count() == 1) {
            $p27 = $p27s->first();
        }
    }
           
    if ($p27) {
        $pMap[$p26->proyecto_id] = $p27->proyecto_id;
    }
}

$acciones2026 = DB::table('acciones_sustantivas')->whereIn('proyecto_id', $proyectos2026->pluck('proyecto_id'))->get();
$acciones2027 = DB::table('acciones_sustantivas')->whereIn('proyecto_id', $proyectos2027->pluck('proyecto_id'))->get();

$aMap = []; // 2026_aid => 2027_aid
foreach ($acciones2026 as $a26) {
    $p27_id = $pMap[$a26->proyecto_id] ?? null;
    if ($p27_id) {
        $a27 = collect($acciones2027)->where('proyecto_id', $p27_id)->first(function($val) use ($a26) {
            return mb_strtolower(trim($val->descripcion)) === mb_strtolower(trim($a26->descripcion)) || 
                   (int)$val->numero === (int)$a26->numero;
        });
        if ($a27) {
            $aMap[$a26->accion_sustantiva_id] = $a27->accion_sustantiva_id;
        }
    }
}

$riesgos2026 = DB::table('riesgos')->where('ejercicio_id', 17)->get();
$clonedCount = 0;
$linkedCount = 0;

foreach ($riesgos2026 as $r26) {
    $r27_id = DB::table('riesgos')->insertGetId([
        'ejercicio_id' => 19,
        'area_id' => $r26->area_id,
        'local_id' => $r26->local_id,
        'riesgo' => $r26->riesgo,
        'objetivo' => $r26->objetivo,
        'factores' => $r26->factores,
        'probabilidad' => $r26->probabilidad,
        'impacto' => $r26->impacto,
        'probabilidad_inicial' => $r26->probabilidad_inicial,
        'impacto_inicial' => $r26->impacto_inicial,
        'status' => 'Captura',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    $clonedCount++;

    $links26 = DB::table('actividad_riesgo')->where('riesgo_id', $r26->id)->get();
    foreach ($links26 as $l26) {
        $a27_id = $aMap[$l26->actividad_sustantiva_id] ?? null;
        if ($a27_id) {
            DB::table('actividad_riesgo')->insert([
                'riesgo_id' => $r27_id,
                'actividad_sustantiva_id' => $a27_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $linkedCount++;
        }
    }
}

echo "Mapped " . count($pMap) . " projects and " . count($aMap) . " actions.\n";
echo "Cloned $clonedCount risks and created $linkedCount links for 2027!\n";
