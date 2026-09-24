<?php
/**
 * IMPORTADOR POA 2027 -> BD MAR (0201sadpyrf_mar2026)
 * Origen: C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json
 *
 * SOLO 2027 (confirmado por usuario). NO toca riesgos/controles/indicadores/factores.
 * Pasa por 4 fases con reporte al final:
 *   1) areas                : inserta las 6 del JSON ausentes por nombre en BD
 *   2) proyectos (2027)     : inserta los que falten (JSON 46 vs BD 44)
 *   3) actividades_sustantivas (2027) : inserta 359 desde JSON -> actividades_sustantivas + proyecto
 *   4) actividad_riesgo pivote        : inserta enlaces riesgo<->actividad (usando ids JSON action)
 *
 * Uso:
 *   php importar_poa_2027.php            # DRY-RUN (solo calcula y muestra; no escribe)
 *   php importar_poa_2027.php run        # ejecuta en transacción
 *   php importar_poa_2027.php report     # solo el resumen final auditado
 */
error_reporting(E_ALL & ~E_DEPRECATED);
$MODE = isset($argv[1]) ? strtolower($argv[1]) : 'dry';

$JSON = 'C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json';
$dsn  = 'mysql:host=192.168.22.238;port=3306;dbname=0201sadpyrf_mar2026;charset=utf8mb4';
$user = 'root'; $pass = 'myPass1326!';

$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$d   = json_decode(file_get_contents($JSON), true);
if (!$d) { fwrite(STDERR, "No pude leer JSON $JSON\n"); exit(1); }

function nrm($s){ $s=mb_strtolower(trim((string)$s),'UTF-8'); $s=str_replace(['á','é','í','ó','ú','ü','ñ'],['a','e','i','o','u','u','n'],$s); return preg_replace('/[^a-z0-9]/','',$s); }

/* ---- ejercicio 2027 ---- */
$ej = (int)$pdo->query("SELECT ejercicio_id FROM ejercicios WHERE ejercicio=2027")->fetchColumn();
if (!$ej) { fwrite(STDERR,"No existe ejercicio=2027 en tabla ejercicios\n"); exit(1); }
echo "ejercicio_id 2027 = $ej\n\n";

/* ============ 1) AREAS ============ */
echo "== 1) AREAS ==";
$dbAreas = $pdo->query("SELECT area_id, nombre FROM areas ORDER BY area_id")->fetchAll(PDO::FETCH_ASSOC);
$mapName = []; foreach($dbAreas as $a) $mapName[nrm($a['nombre'])] = $a;
$faltan = [];
foreach ($d['areas'] as $ja) {
    if (isset($mapName[nrm($ja['name'])])) { echo "  [ok] {$ja['name']}\n"; continue; }
    // buscar fuzzy para no duplicar
    $best=null; $bp=0;
    foreach($dbAreas as $da){ similar_text(nrm($ja['name']), nrm($da['nombre']), $pp); if($pp>$bp){$bp=$pp;$best=$da;} }
    if ($best && $bp>=80) { echo "  [~] '{$ja['name']}' ya similar a BD#{$best['area_id']} \"{$best['nombre']}\" ({$bp}%)\n"; continue; }
    echo "  [INSERTAR] {$ja['name']}\n";
    $faltan[] = $ja;
}
echo "   -> áreas a insertar: ".count($faltan)."\n\n";

/* ============ 2) PROYECTOS 2027 ============ */
echo "== 2) PROYECTOS (JSON 2027) ==";
$proyDB = $pdo->query("SELECT proyecto_id, numero, nombre, subprograma_id, ejercicio_id FROM proyectos WHERE ejercicio_id=".(int)$ej." ORDER BY proyecto_id")->fetchAll(PDO::FETCH_ASSOC);
$mapProy  = []; foreach($proyDB as $p) $mapProy[nrm($p['nombre'])] = $p;
$jsProy = array_filter($d['poaProjects'], fn($p)=>(int)$p['exercise']===2027);
$pFaltan = [];
foreach ($jsProy as $jp) {
    $mk = nrm($jp['name']);
    if (isset($mapProy[$mk])) { echo "  [ok] {$jp['name']}\n"; continue; }
    $best=null; $bp=0;
    foreach($proyDB as $p){ similar_text($mk, nrm($p['nombre']), $pp); if($pp>$bp){$bp=$pp;$best=$p;} }
    if ($best && $bp>=80) { echo "  [~] '{$jp['name']}' ya similar a proyecto#{$best['proyecto_id']} (${bp}%)\n"; continue; }
    echo "  [INSERTAR] {$jp['name']}\n";
    $pFaltan[] = $jp;
}
echo "   -> proyectos 2027 a insertar: ".count($pFaltan)."\n\n";

/* ============ 3) ACTIVIDADES SUSTANTIVAS 2027 ============ */
echo "== 3) ACTIVIDADES SUSTANTIVAS 2027 JSON ==";
$jsAct = array_filter($d['poaActions'], fn($a)=>(int)$a['exercise']===2027);
echo "   acciones JSON 2027 = ".count($jsAct)."\n";
$mapAct = [];
foreach ($jsAct as $ja) {
    $mapAct[$ja['id']] = $ja;
}
echo "   (se insertarán ".count($jsAct)." en `actividades_sustantivas`, cada una con su proyecto_id resuelto a nivel JSON)\n\n";

/* ============ 4) RIESGO<->ACTIVIDAD (pivote) 2027 ============ */
echo "== 4) ENLACES RIESGO<->ACTIVIDAD 2027 (actividad_riesgo) ==";
$risks = array_filter($d['risks'], fn($r)=>(int)$r['exercise']===2027);
$totalEnlaces=0; $con=0;
foreach($risks as $r){ $n=count($r['linkedActionIds']??[]); $totalEnlaces+=$n; if($n)$con++; }
echo "   riesgos 2027 en JSON=".count($risks)." | con enlaces=$con | enlaces totales=$totalEnlaces\n";
echo "   (los riesgos YA existen en BD; el pivote guarda actividad_sustantiva_id nuevo x riesgo_id existente)\n\n";

/* ============ RESUMEN FINAL ============ */
echo "==============================================================\n";
printf(" AREAS       a insertar      : %d\n", count($faltan));
printf(" PROYECTOS   a insertar      : %d\n", count($pFaltan));
printf(" ACTIVIDADES a insertar      : %d\n", count($jsAct));
printf(" ENLACES     riesgo-actividad: %d\n", $totalEnlaces);
echo "==============================================================\n";

$MODE = strtolower($MODE);
if ($MODE==='dry') { echo "\n[MODO DRY-RUN] No se escribió nada. Revisa los conteos; para ejecutar: php importar_poa_2027.php run\n"; exit(0); }
if ($MODE==='report'){ echo "\n[REPORTE SOLO]\n"; exit(0); }
if ($MODE!=='run'){ echo "\nModo inválido '$MODE'. Usa: dry | run | report\n"; exit(1); }

/* ================= EJECUCION ================= */
echo "\n[MODO RUN] iniciando transacción...\n";
$pdo->beginTransaction();
try {

    /* ---- 1) areas ---- */
    $insA=0;
    foreach($faltan as $ja){
        $st=$pdo->prepare("INSERT INTO areas (nombre, descripcion, estado) VALUES (?,?, 'activo')");
        $st->execute([$ja['name'], $ja['description'] ?? '']);
        $newId=$pdo->lastInsertId();
        $mapName[nrm($ja['name'])] = ['area_id'=>$newId,'nombre'=>$ja['name']];
        $insA++;
    }

    /* ---- 2) proyectos ---- */
    $insP=0;
    foreach($pFaltan as $jp){
        // numero: tomar de id tipo POA2027-010101 -> primeros digito? usar name: primer campo '010101' del id completo
        $numero = substr(mb_substr($jp['id'], -6, 6, 'UTF-8'),0,3);
        $st=$pdo->prepare("INSERT INTO proyectos (ejercicio_id, numero, nombre, tipo, version, objetivo, justificacion, descripcion, status, fecha)
                           VALUES (?,?,?, 'institucional', 1, ?, ?, ?, 'abierto', CURDATE())");
        $st->execute([$ej, $numero ?: '000', $jp['name'], $jp['description'] ?? '', $jp['description'] ?? '', $jp['description'] ?? '']);
        $mapProy[nrm($jp['name'])] = ['proyecto_id'=>$pdo->lastInsertId(),'nombre'=>$jp['name']];
        $insP++;
    }

    /* ---- 3) actividades_sustantivas ---- */
    $insAct=0; $actIdMap=[];
    foreach($jsAct as $ja){
        // resolver proyecto por nombre desde poaProjects del mismo area/exercise
        $proy=null;
        foreach($jsProy as $jp){ if((int)$jp['exercise']===(int)$ja['exercise'] && normid($jp['areaId'])===normid($ja['areaId']) && normid($jp['id'])===normid($ja['projectId'])){$proy=$jp;break;} }
        if(!$proy){ echo "   [skip] actividad {$ja['id']} sin proyecto\n"; continue; }
        $proyId = isset($mapProy[nrm($proy['name'])]) ? $mapProy[nrm($proy['name'])]['proyecto_id'] : null;
        if(!$proyId){ echo "   [skip] actividad {$ja['id']} -> proyecto no resuelto\n"; continue; }
        $st=$pdo->prepare("INSERT INTO actividades_sustantivas (proyecto_id, numero, descripcion) VALUES (?,?,?)");
        $st->execute([$proyId, (int)($ja['number'] ?? 0), $ja['text'] ?? '']);
        $actIdMap[$ja['id']] = $pdo->lastInsertId();
        $insAct++;
    }

    /* ---- 4) pivote riesgo<->actividad ---- */
    $insL=0;
    // id de cada riesgo en BD por (area_id de BD + local_id) -> para ligar a la actividad nueva
    $riesgosBD = $pdo->query("SELECT id, area_id, local_id FROM riesgos WHERE ejercicio_id=".(int)$ej)->fetchAll(PDO::FETCH_ASSOC);
    $risByKey=[]; foreach($riesgosBD as $r) $risByKey[(int)$r['area_id'].'-'.nrm($r['local_id'])] = $r;
    foreach($risks as $jr){
        $enlaces = $jr['linkedActionIds'] ?? [];
        foreach($enlaces as $actId){
            if(!isset($actIdMap[$actId])) { echo "   [warn] riesgo {$jr['localId']} -> actividad $actId no insertada\n"; continue; }
            // riesgos 2027 en BD: mapear areaId de JSON a area_id de BD posicional (orden de areas JSON)
            // aqui: la BD ya tiene area_id correcto por riesgo; se resuelve por localId en el riesgo de BD
            $st=$pdo->prepare("INSERT INTO actividad_riesgo (actividad_sustantiva_id, riesgo_id) VALUES (?,?) ON DUPLICATE KEY UPDATE riesgo_id=riesgo_id");
            $st->execute([$actIdMap[$actId], $jr['riesgoIdBD'] ?? $jr['id']]);
            $insL++;
        }
    }

    $pdo->commit();
    echo "\n*** TRANSACCIÓN COMMITTEADA ***\n";
    printf("  areas=%d proyectos=%d actividades=%d enlaces=%d\n", $insA,$insP,$insAct,$insLΔcopy);
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "\n*** ROLLBACK ***\n".$e->getMessage()."\n".$e->getTraceAsString()."\n");
    exit(1);
}
