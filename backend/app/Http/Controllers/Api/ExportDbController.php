<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExportDbController extends Controller
{
    public function export(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        try {
            $tablesStmt = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $tables = array_map(fn($t) => array_values((array)$t)[0], $tablesStmt);

            $estructura = [];
            $tablas = [];
            $sqlStatements = [];

            $sqlStatements[] = "-- ========================================================";
            $sqlStatements[] = "-- RESPALDO COMPLETO MAR TECDMX";
            $sqlStatements[] = "-- FECHA: " . now()->toDateTimeString();
            $sqlStatements[] = "-- ========================================================";
            $sqlStatements[] = "SET NAMES utf8mb4;";
            $sqlStatements[] = "SET FOREIGN_KEY_CHECKS = 0;";
            $sqlStatements[] = "";

            foreach ($tables as $table) {
                // Estructura (CREATE TABLE)
                try {
                    $createRow = DB::select("SHOW CREATE TABLE `{$table}`");
                    $createSql = array_values((array)$createRow[0])[1];
                    $estructura[$table] = $createSql;
                    $sqlStatements[] = "DROP TABLE IF EXISTS `{$table}`;";
                    $sqlStatements[] = $createSql . ";";
                } catch (\Exception $e) {
                    $estructura[$table] = null;
                }

                // Datos
                try {
                    $rows = DB::table($table)->get();
                    $tablas[$table] = $rows->toArray();

                    if ($rows->count() > 0) {
                        $batch = [];
                        foreach ($rows as $row) {
                            $vals = [];
                            foreach ((array)$row as $val) {
                                if (is_null($val)) {
                                    $vals[] = 'NULL';
                                } elseif (is_numeric($val) && !is_string($val)) {
                                    $vals[] = $val;
                                } else {
                                    $vals[] = DB::getPdo()->quote($val);
                                }
                            }
                            $batch[] = '(' . implode(', ', $vals) . ')';
                            if (count($batch) >= 200) {
                                $sqlStatements[] = "INSERT INTO `{$table}` VALUES \n" . implode(",\n", $batch) . ";";
                                $batch = [];
                            }
                        }
                        if (!empty($batch)) {
                            $sqlStatements[] = "INSERT INTO `{$table}` VALUES \n" . implode(",\n", $batch) . ";";
                        }
                    }
                    $sqlStatements[] = "";
                } catch (\Exception $e) {
                    $tablas[$table] = [];
                }
            }

            $sqlStatements[] = "SET FOREIGN_KEY_CHECKS = 1;";

            // Desglose por ejercicios (para compatibilidad con herramientas específicas del MAR)
            $ejerciciosDb = DB::table('ejercicios')->whereIn('ejercicio', ['2026', '2027'])->get();
            $datosEjercicios = [];

            foreach ($ejerciciosDb as $ej) {
                $ejercicioId = $ej->ejercicio_id;
                $year = (string)$ej->ejercicio;

                $riesgos = DB::table('riesgos')->where('ejercicio_id', $ejercicioId)->get();
                $riesgoIds = $riesgos->pluck('id')->toArray();

                $riesgosInst = DB::table('riesgos_institucionales')->where('ejercicio_id', $ejercicioId)->get();
                $riIds = $riesgosInst->pluck('id')->toArray();

                $riFuentes = !empty($riIds)
                    ? DB::table('riesgo_institucional_fuente')->whereIn('riesgo_institucional_id', $riIds)->get()
                    : collect([]);

                $proyectos = DB::table('proyectos')->where('ejercicio_id', $ejercicioId)->pluck('proyecto_id')->toArray();
                $actividades = !empty($proyectos)
                    ? DB::table('actividades_sustantivas')->whereIn('proyecto_id', $proyectos)->get()
                    : collect([]);

                $controles = !empty($riesgoIds)
                    ? DB::table('riesgo_controles')->whereIn('riesgo_id', $riesgoIds)->get()
                    : collect([]);

                $riesgoIndicadores = !empty($riesgoIds)
                    ? DB::table('riesgo_indicadores')->whereIn('riesgo_id', $riesgoIds)->get()
                    : collect([]);

                $indicadores = !empty($proyectos)
                    ? DB::table('indicadores')->whereIn('proyecto_id', $proyectos)->get()
                    : collect([]);

                $urgs = [];
                try {
                    $urgs = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', $ejercicioId)->get()->toArray();
                } catch (\Exception $e) {
                    $urgs = DB::table('unidades_responsables_gastos')->get()->toArray();
                }

                $datosEjercicios[$year] = [
                    'riesgos' => $riesgos->toArray(),
                    'riesgos_institucionales' => $riesgosInst->toArray(),
                    'riesgo_institucional_fuente' => $riFuentes->toArray(),
                    'controles' => $controles->toArray(),
                    'indicadores' => $indicadores->toArray(),
                    'riesgo_indicadores' => $riesgoIndicadores->toArray(),
                    'actividades' => $actividades->toArray(),
                    'unidades_responsables_gastos' => $urgs,
                    'responsables_operativos' => DB::table('responsables_operativos')->get()->toArray(),
                    'configuracion_trimestres' => DB::table('configuracion_trimestres')->where('ejercicio_id', $ejercicioId)->get()->toArray(),
                    'seguimiento_trimestrals' => DB::table('seguimiento_trimestrals')->where('ejercicio_id', $ejercicioId)->get()->toArray(),
                ];
            }

            if ($request->query('format') === 'sql') {
                $sqlContent = implode("\n", $sqlStatements);
                return response($sqlContent, 200, [
                    'Content-Type' => 'application/sql',
                    'Content-Disposition' => 'attachment; filename="Respaldo_MAR_TECDMX_' . date('Y-m-d') . '.sql"',
                ]);
            }

            $response = [
                'schemaVersion' => '2.0',
                'appName' => 'MAR_TECDMX',
                'backupDate' => now()->toIso8601String(),
                'totalTablas' => count($tables),
                'ejercicios' => ['2026', '2027'],
                'estructura' => $estructura,
                'tablas' => $tablas,
                'datos' => $datosEjercicios,
                'settings' => DB::table('settings')->get()->toArray(),
                'sqlDump' => implode("\n", $sqlStatements),
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error exportando BD: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno al exportar base de datos: ' . $e->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        try {
            $file = $request->file('file');
            if (!$file && !$request->has('sql') && !$request->has('json')) {
                return response()->json(['error' => 'No se proporcionó ningún archivo o contenido para importar.'], 400);
            }

            $content = '';
            $extension = '';

            if ($file) {
                $extension = strtolower($file->getClientOriginalExtension());
                $content = file_get_contents($file->getRealPath());
            } elseif ($request->has('json')) {
                $content = $request->input('json');
                $extension = 'json';
            } elseif ($request->has('sql')) {
                $content = $request->input('sql');
                $extension = 'sql';
            }

            if ($extension === 'json') {
                $data = json_decode($content, true);
                if (!$data || !is_array($data)) {
                    return response()->json(['error' => 'El archivo JSON no tiene un formato válido.'], 400);
                }

                if (!empty($data['sqlDump'])) {
                    DB::unprepared($data['sqlDump']);
                    return response()->json([
                        'message' => 'Base de datos importada exitosamente.',
                        'totalTablas' => $data['totalTablas'] ?? 'N/A',
                        'backupDate' => $data['backupDate'] ?? 'N/A',
                    ]);
                }
            } elseif ($extension === 'sql') {
                DB::unprepared($content);
                return response()->json([
                    'message' => 'Sentencias SQL ejecutadas e importadas exitosamente.',
                ]);
            }

            return response()->json(['error' => 'Formato no soportado o archivo sin sentencias válidas.'], 400);
        } catch (\Exception $e) {
            Log::error('Error importando BD: ' . $e->getMessage());
            return response()->json(['error' => 'Error al importar la base de datos: ' . $e->getMessage()], 500);
        }
    }
}
