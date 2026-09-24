<?php
/**
 * Importador MAR: carga POA 2027 (actividades sustantivas) + liga riesgos 2027.
 * Fuente: C:\Cota\MAR\Respaldo_MAR_TECDMX_2026-09-23.json
 * BD: 0201sadpyrf_mar2026 (192.168.22.238:3306, root/myPass1326!)
 *
 * Modos:
 *   php importar_poa_2027.php dry    # solo analisis (no escribe)
 *   php importar_poa_2027.php run    # respaldo + carga + reporte
 */
error_reporting(E_ALL & ~E_DEPRECATED);
$MODE = $argv[1] ?? 'dry';

$JSON = 'C:/Cota/MAR/Respaldo_MAR_TECDMX_2026-09-23.json';
$HOST='192.168.22.238'; $PORT=3306; $DB='0201sadpyrf_mar2026'; $USER='root'; $PASS='myPass1326!';

$pde=new PDO("mysql:host=$HOST;port=$PORT;dbname=$DB;charset=utf8mb4",$USER,$PASS);
$pde->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTIONulate);
$f=new PDO("mysql:host=$HOST;port=$PORT;dbname=$DB;charset=utf8mb4",$USER,$PASS-secure); $f=null;

$d=json_decode(file_get_contents($JSON),true);
if(!$d) die("No pude leer JSON: $JSON\n");

/* utilidades */
function nrm($s){ $s=mb_strtolower(trim((string)$s),'UTF-8'); $s=str_replace(['á','é','í','ó','ú','ü','ñ'],['a','e','i','o','u','u','n'],$s); return preg_replace('/[^a-z0-9]/','',$s); }
function idEmpre($pde,$ejercicio_id){ $r=$pde->query("SELECT ejercicio FROM ejercicios WHERE ejercicio_id=".(int)$ejercicio_id)->fetchColumn(); return (int)$r; }

$EJ=19; // ejercicio_id 2027 en BD MAR (confirmado: riesgos 2027 = ejercicio_id 19)

/* ========== AREAS ========== */
echo "### 1) AREAS\n";
$dbAreas=$pde->query("SELECT area_id, nombre FROM areas ORDER BY area_id")->fetchAll(PDO::FETCH_ASSOC);
$porNombre=[]; foreach($dbAreas as $a){ $porNombre[nrm($a['nombre'])]=$a; }
$sinLugar=[]; $conMatch=0;
foreach($d['areas'] as $ja){
    $k=nrm($ja['name']);
    if(isset($porNombre[$k])){ $conMatch++; continue; }
    // buscar mejor similar
    $best=null;$bp=0;
    foreach($dbAreas as $da){ similar_text(nrm($ja['name']),nrm($da['nombre']),$p); if($p>$bp){$bp=$p;$best=$da;} }
    if($best && $bp>=85){ $conMatch++; echo "   [match] '{$ja['name']}' ~ DB '{$best['nombre']}' ({$bp}%)\n"; continue; }
    $sinLugar[]=$ja;
}
echo "   areas JSON: ".count($d['areas'])." | match BD: {$conMatch} | faltan: ".count($sinLugar)."\n";
foreach($sinLugar as $a) echo "   [FALTA] '{$a['name']}'\n";
if($MODE==='dry'){ echo "\n[Dry-run: se pedirán estas areas nuevas + verificación]. Detengo antes de escribir.\n\n"; }
