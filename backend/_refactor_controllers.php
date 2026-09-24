<?php
$files = [
    'c:/cota/MAR/backend/app/Models/ActividadSustantiva.php',
    'c:/cota/MAR/backend/app/Http/Controllers/Api/RiesgoController.php',
    'c:/cota/MAR/backend/app/Http/Controllers/Api/POAFichasController.php',
    'c:/cota/MAR/backend/app/Http/Controllers/Api/ProyectoController.php',
    'c:/cota/MAR/backend/app/Http/Controllers/Api/ModoController.php',
    'c:/cota/MAR/backend/app/Http/Controllers/Api/ActividadSustantivaController.php'
];

// ActividadSustantiva.php
$c = file_get_contents($files[0]);
$c = str_replace("protected \$table = 'acciones_sustantivas';", "protected \$table = 'actividades_sustantivas';", $c);
file_put_contents($files[0], $c);

// RiesgoController.php
$c = file_get_contents($files[1]);
$c = preg_replace("/\\\$viejas = DB::connection\\('poa_prod'\\)->table\\('acciones_sustantivas'\\).*?;/s", "", $c);
$c = preg_replace("/\\\$viejasArea = DB::connection\\('poa_prod'\\)->table\\('acciones_sustantivas'\\).*?;/s", "", $c);
$c = str_replace('$actividades = $nuevas->merge($viejas);', '$actividades = $nuevas;', $c);
$c = str_replace('$actividadesArea = $nuevasArea->merge($viejasArea);', '$actividadesArea = $nuevasArea;', $c);
file_put_contents($files[1], $c);

// POAFichasController.php
$c = file_get_contents($files[2]);
$c = preg_replace("/if \\(\\\$actividades->isEmpty\\(\\)\\) \\{\\s+\\\$actividades = DB::table\\('acciones_sustantivas'\\)->where\\('proyecto_id', \\\$p->id\\)->get\\(\\);\\s+\\}/", "", $c);
file_put_contents($files[2], $c);

// ProyectoController.php
$c = file_get_contents($files[3]);
$c = str_replace("\$table = (\$proyecto && (int)\$proyecto->ejercicio >= 2027) ? 'actividades_sustantivas' : 'acciones_sustantivas';", "\$table = 'actividades_sustantivas';", $c);
$c = str_replace("\$pk = (\$table === 'actividades_sustantivas') ? 'id' : 'accion_sustantiva_id';", "\$pk = 'id';", $c);
$c = str_replace("DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', \$pid)->delete();", "", $c);
file_put_contents($files[3], $c);

// ModoController.php
$c = file_get_contents($files[4]);
$c = preg_replace("/\\\$actividades = DB::connection\\('poa_prod'\\)->table\\('acciones_sustantivas'\\).*?get\\(\\);\\s+if \\(\\\$actividades->isEmpty\\(\\)\\) \\{\\s+\\\$actividades = DB::connection\\('poa_prod'\\)->table\\('actividades_sustantivas'\\).*?get\\(\\);\\s+\\}/s", "\$actividades = DB::connection('poa_prod')->table('actividades_sustantivas')->whereIn('proyecto_id', array_keys(\$mapPy))->get();", $c);
$c = str_replace("\$targetTable = (\$ejercicioDestino >= 2027) ? 'actividades_sustantivas' : 'acciones_sustantivas';", "\$targetTable = 'actividades_sustantivas';", $c);
file_put_contents($files[4], $c);

// ActividadSustantivaController.php
$c = file_get_contents($files[5]);
$c = preg_replace("/private function getTableForProyecto\\(\\\$proyectoId\\) \\{.*?return 'actividades_sustantivas';\\s+\\}/s", "private function getTableForProyecto(\$proyectoId) {\n        return 'actividades_sustantivas';\n    }", $c);
$c = preg_replace("/private function getTableForActividad\\(\\\$id\\) \\{.*?return 'actividades_sustantivas';\\s+\\}/s", "private function getTableForActividad(\$id) {\n        return 'actividades_sustantivas';\n    }", $c);
$c = str_replace("return (\$proyecto && (int)\$proyecto->ejercicio >= 2027) ? 'actividades_sustantivas' : 'acciones_sustantivas';", "return 'actividades_sustantivas';", $c);
$c = str_replace("\$pk = (\$table === 'actividades_sustantivas') ? 'id' : 'accion_sustantiva_id';", "\$pk = 'id';", $c);
$c = str_replace("return 'acciones_sustantivas';", "return 'actividades_sustantivas';", $c);
file_put_contents($files[5], $c);

echo "Refactored controllers.\n";
