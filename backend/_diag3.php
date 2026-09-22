<?php require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

function limpiar($s) {
    $s = preg_replace('/[áàäâ]/u', 'a', mb_strtolower(trim($s)));
    $s = preg_replace('/[éèëê]/u', 'e', $s);
    $s = preg_replace('/[íìïî]/u', 'i', $s);
    $s = preg_replace('/[óòöô]/u', 'o', $s);
    $s = preg_replace('/[úùüû]/u', 'u', $s);
    $s = str_replace(['director', 'directora'], 'direccion', $s);
    $s = str_replace(['presidente', 'presidenta'], 'presidencia', $s);
    $s = str_replace(['secretario', 'secretaria'], 'secretaria', $s);
    $s = str_replace(['contralor', 'contralora'], 'contraloria', $s);
    $s = str_replace(['interno', 'interna'], 'interna', $s);
    $s = str_replace(['defensor', 'defensora'], 'defensoria', $s);
    $s = str_replace(['ciudadano', 'ciudadana'], 'ciudadana', $s);
    return $s;
}

foreach ([17, 19] as $ej) {
    echo "\n############### EJERCICIO_DB $ej ###############\n";
    // build mapa POA urg -> URG id
    $mapa = [];
    $todasUrgs = DB::table('unidades_responsables_gastos')->get();
    $rosEjercicio = DB::table('responsables_operativos')->where('ejercicio_id', $ej)->get()->groupBy('unidad_responsable_gasto_id');
    foreach ($todasUrgs as $u) {
        $palabras = array_filter(explode(' ', preg_replace('/[^\p{L}\p{N} ]/u', ' ', limpiar($u->nombre))), fn($p) => mb_strlen($p) > 5);
        foreach ($rosEjercicio as $urgIdPoa => $rosGrupo) {
            foreach ($rosGrupo as $ro) {
                $c = 0;
                foreach ($palabras as $palabra) if (str_contains(limpiar($ro->nombre), $palabra)) $c++;
                if ($c >= 2 || (count($palabras) === 1 && $c >= 1)) { $mapa[(int)$urgIdPoa] = $u; break 2; }
            }
        }
    }

    $proyectos = DB::table('proyectos')
        ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
        ->where('proyectos.ejercicio_id', $ej)
        ->select('proyectos.*', 'responsables_operativos.unidad_responsable_gasto_id as urg_id')
        ->get()
        ->groupBy('urg_id');

    foreach ($proyectos as $urgIdPoa => $grupo) {
        $u = $mapa[$urgIdPoa] ?? null;
        $nombre = $u ? mb_substr($u->nombre, 0, 40) : "POA-#$urgIdPoa";
        $acciones = [];
        foreach ($grupo as $p) {
            $acts = DB::table('actividades_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
            if ($acts->isEmpty()) $acts = DB::table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->get();
            foreach ($acts as $a) $acciones[] = $a;
        }
        $fila = []; $tot = 0;
        foreach ($acciones as $a) {
            $actId = $a->id ?? $a->accion_sustantiva_id ?? null;
            $n = $actId ? DB::table('actividad_riesgo')->where('actividad_sustantiva_id', $actId)->count() : 0;
            $fila[] = $n; $tot += $n;
        }
        $nAcc = count($acciones);
        $nRiesgos = $u ? DB::table('riesgos')->where('ejercicio_id', $ej)->where('area_id', $u->unidad_responsable_gasto_id)->count() : 0;
        if ($nAcc === 0) { echo sprintf("%-4s %-40s sin actividades\n", '#'.$urgIdPoa, $nombre); continue; }
        $tipo = '';
        if ($nRiesgos > 0 && $nAcc > 0) {
            $todosUno = count(array_unique($fila)) === 1 && $fila[0] === 1 && $nAcc === $nRiesgos;
            if ($todosUno) $tipo = '1:1';
            elseif (count(array_unique($fila)) === 1 && $fila[0] > 1) $tipo = "N x N (cada act={$fila[0]} riesgos)";
            elseif (in_array(0, $fila, true)) $tipo = 'mixto / con vacíos';
            else $tipo = '1:N / N:M';
        } elseif ($nRiesgos === 0) $tipo = 'sin riesgos del área';
        else $tipo = 'mixto';
        echo sprintf("%-4s %-40s act:%-2d riesgos:%-2d  [%s]  -> %s\n", '#'.$urgIdPoa, $nombre, $nAcc, $nRiesgos, implode(',', $fila), $tipo);
    }
}