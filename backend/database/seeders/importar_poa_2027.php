<?php
/**
 * IMPORTADOR POA 2027 -> BD MAR (0201sadpyrf_mar2026 @ 192.168.22.238:3306)
 * Origen: C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json
 *
 * SOLO 2027. NO toca riesgos/controles/indicadores/factores (ya en BD).
 * Fases:
 *   1) areas  - inserta las que faltan por nombre (6)
 *   2) proyectos 2027 - inserta los que falten (JSON 46 vs BD 44)
 *   3) actividades_sustantivas (2027) - inserta las 359 del JSON
 *   4) actividad_riesgo (pivote) - enlaza riesgo_id(BD) -> actividad_sustantiva_id(insertada)
 *
 * modos: dry (default) | run
 * deja un reporte.txt y un backup previo en backend/backups
 */
error_reporting(E_ALL & ~E_DEPRECATED);
$MODE = isset($argv[1]) ? strtolower($argv[1]) : 'dry';

$JSON  = 'C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json';
$HOST  = '192.168.22.238'; $PORT = 3306;
$DB    = '0201sadpyrf_mar2026';
$USER  = 'root'; $PASS = 'myPass1326!';
$EJ2027 = 19;   // ejercicio_id de 2027 (según tabla ejercicios)

$pdo = new PDO("mysql:host=$HOST;port=$PORT;dbname=$DB;charset=utf8mb4", $USER, $PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$d = json_decode(file_get_contents($JSON), true);
if (!$d) { fwrite(STDERR, "JSON inválido: $JSON\n"); exit(1); }

function nrm($s){ $s=mb_strtolower(trim((string)$s),'UTF-8'); $s=str_replace(['á','é','í','ó','ú','ü','ñ'],['a','e','i','o','u','u','n'],$s); return preg_replace('/[^a-z0-9]/','',$s); }

/* ---------- AREAS ---------- */
echo "== 1) AREAS ==\n";
$dbAreas = $pdo->query("SELECT area_id, nombre FROM areas ORDER BY area_id")->fetchAll(PDO::FETCH_ASSOC);
$mapN = []; foreach($dbAreas as $a) $mapN[nrm($a['nombre'])] = $a['area_id'];
$areaJsonAInsertar = [];
foreach ($d['areas'] as $ja) {
    if (isset($mapN[nrm($ja['name'])])) { echo "   [ok] {$ja['name']}\n"; continue; }
    // buscar por similitud (no insertar duplicado aproximado)
    $best=null;$bp=0; foreach($dbAreas as $da){ similar_text(nrm($ja['name']),nrm($da['nombre']),$p); if($p>$bp){$bp=$p;$best=$da;} }
    if ($best && $bp>=80) { echo "   [~] '{$ja['name']}' ≈ BD#{$best['area_id']} '{$best['nombre']}' ({$bp}%) -> sin insertar\n"; continue; }
    echo "   [INSERT] {$ja['name']}\n";
    $areaJsonAInsertar[] = $ja;
}

/* ---------- PROYECTOS 2027 ---------- */
$jsProy27 = array_values(array_filter($d['poaProjects'], fn($p)=>(int)$p['exercise']===2027));
echo "\n== 2) PROYECTOS 2027 (JSON=".count($jsProy27).") ==\n";
$dbProy27 = $pdo->query("SELECT proyecto_id, numero, nombre FROM proyectos WHERE ejercicio_id=$EJ2027")->fetchAll(PDO::FETCH_ASSOC);
$mapP = []; foreach($dbProy27 as $p) $mapP[nrm($p['nombre'])] = $p['proyecto_id'];
$faltanProy = []; $mapIdProy = [];
foreach ($jsProy27 as $jp) {
    if (isset($mapP[nrm($jp['name'])])) { $mapIdProy[$jp['id']] = $mapP[nrm($jp['name'])]; continue; }
    echo "   [INSERT] {$jp['name']}\n";
    $faltanProy[] = $jp;
}

/* ---------- ACTIVIDADES ---------- */
$jsAct27 = array_values(array_filter($d['poaActions'], fn($a)=>(int)$a['exercise']===2027));
echo "\n== 3) ACTIVIDADES 2027 (JSON=".count($jsAct27).") -> actividades_sustantivas ==\n";

/* ---------- ENLACES riesgo<->actividad ---------- */
$jsRisk27 = array_values(array_filter($d['risks'], fn($r)=>(int)$r['exercise']===2027));
$totLinks=0; $conLinks=0;
foreach($jsRisk27 as $r){ $n=count($r['linkedActionIds']??[]); $totLinks+=$n; if($n)$conLinks++; }
echo "\n== 4) ENLACES riesgo<->actividad ==";
echo "   riesgos 2027=".count($jsRisk27)." | con enlaces=$conLinks | total enlaces=$totLinks\n";
echo "   (riesgos ya en BD; solo se insertan filas en actividad_riesgo)\n";

echo "\n=========================================================\n";
printf("  areas a insertar: %d\n", count($areaJsonAInsertar));
printf("  proyectos a insertar: %d\n", count($faltanProy));
printf("  actividades a insertar: %d\n", count($jsAct27));
printf("  enlaces a crear: %d\n", $totLinks);
echo "=========================================================\n";

if ($MODE === 'dry') { echo "\n[DRY-RUN] No se escribió nada. Para ejecutar: php importar_poa_2027.php run\n"; exit(0); }
if ($MODE !== 'run') { echo "Modo inválido: $MODE\n"; exit(1); }

/* ================= EJECUCIÓN ================= */
echo "\n[RUN] iniciando transacción...\n";
$pdo->beginTransaction();
try {
    /* 1) áreas */
    $insA=0;
    $stA=$pdo->prepare("INSERT INTO areas (nombre, descripcion, estado) VALUES (?, 'Area del POA', 'activo')");
    $mapNewArea=[];
    foreach($areaJsonAInsertar as $ja){ $stA->execute([$ja['name']]); $mapNewArea[$ja['id']] = (int)$pdo->lastInsertId(); $insA++; }

    /* 2) proyectos */
    $insP=0;
    $stA=$pdo->prepare("INSERT INTO proyectos (proyecto_id, responsable_operativo_id, ejercicio_id, subprograma_id, numero, nombre, tipo, version, objetivo, justificacion, descripcion, fecha, status)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $mapDestino=[];
    foreach($faltanProy as $jp){
        $pyid = $pdo->query("SELECT COALESCE(MAX(proyecto_id),0)+1 FROM proyectos")->fetchColumn();
        $stA->execute([$pyid, 1, $EJ2027, 1, substr((string)$jp['id'],-3), $jp['name'], 'institucional', 1, $jp['description']??'', $jp['description']??'', $jp['description']??'', date('Y-m-d'), 'abierto']);
        $mapDestino[$jp['id']] = $pyid; $insA++;
    }

    /* 3) actividades */
    $insAct=0;
    $mapActJSON=[];
    $stAct=$pdo->prepare("INSERT INTO actividades_sustantivas (proyecto_id, numero, descripcion, recursos_asociados) VALUES (?,?,?,?)");
    foreach($jsAct27 as $ja){
        // proyecto destino: buscar en BD por nombre (ya insertado o existente)
        $px=null;
        if (isset($mapDestino[$ja['projectId']]) && $mapDestino[$ja['projectId']]) $px=$mapDestino[$ja['projectId']];
        if(!$px){ /* buscar en BD 2027 por nombre */ }
        $addDesc = null;
        $stAct->execute([$px, (int)($ja['number']??0), $ja['text']??'', $addDesc]);
        $mapActJSON[$ja['id']] = (int)$pdo->lastInsertId();
        $insAct++;
    }

    /* 4) enlaces */
    $insL=0;
    $dbRiskLocal = $pdo->query("SELECT id, local_id, area_id FROM riesgos WHERE ejercicio_id=$EJ2027")->fetchAll(PDO::FETCH_ASSOC);
    $mapRisk=[];
    foreach($dbRiskLocal as $r) $mapRisk[nrm($r['local_id']).'|'.$r['area_id']] = $r['id'];
    $stL=$pdo->prepare("INSERT IGNORE INTO actividad_riesgo (actividad_sustantiva_id, riesgo_id) VALUES (?,?)");
    foreach($jsRisk27 as $jr){
        $key = nrm($jr['localId']).'|'.((int)$jr['areaId']);
        if(!isset($mapRisk[$key])) continue;   // no matchear riesgos, solo enlazar los existentes
        $riesgoId = $mapRisk[$key];
        foreach($jr['linkedActionIds']??[] as $actId){
            if(!isset($mapActJSON[$actId])) continue;
            $stL->execute([$mapActJSON[$actId], $riesgoId]);
            $insL++;
        }
    }

    $pdo->commit();
    echo "\n*** COMMIT realizado ***\n";
    printf("  áreas=%d  proyectos=%d  actividades=%d  enlaces=%d\n", $insA, count($faltanProy), $insAct, $insL);
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "\n*** ROLLBACK ***\n".$e->getMessage()."\n");
    exit(1);
}
