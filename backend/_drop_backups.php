<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$dbName = 'Tables_in_' . env('DB_DATABASE');

$allTables = [];
foreach($tables as $t) {
    $tableName = $t->{$dbName} ?? array_values((array)$t)[0];
    $allTables[] = $tableName;
}

$patternsToDrop = [
    'acciones_sustantivas_bkp_11sep2026',
    'actividades_sustantivas_bkp_11sep2026',
    'backup_actividad_riesgo_20260924_013211',
    'indicadores_bkp_11sep2026',
    'metas_bkp_11sep2026',
    'model_has_roles_bak_20260813_',
    'proyectos_backup_21082026',
    'proyectos_bkp_11sep2026',
    'subprogramas_backup_21082026',
    'usuario_unidad_responsable_bak_20260813_',
    'usuarios_poa_bak_20260813_',
    'usuarios_responsables_operativos_bak_20260813_',
    'acciones_sustantivas_derechos_humanos_bkp_11sep2026',
    'equidades_generos_bkp_11sep2026',
    'meses_metas_alcanzadas_bkp_11sep2026',
    'meses_metas_programadas_bkp_11sep2026',
    'meses_proyectos_bkp_11sep2026',
    'pei_proyecto_alineaciones_bkp_11sep2026'
];

$tablesToDrop = [];

foreach ($allTables as $table) {
    foreach ($patternsToDrop as $pattern) {
        // If the table name starts with the pattern, we mark it for deletion
        if (strpos($table, $pattern) === 0) {
            $tablesToDrop[] = $table;
            break;
        }
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

foreach ($tablesToDrop as $table) {
    Schema::drop($table);
    echo "Tabla de respaldo eliminada: $table\n";
}

DB::statement('SET FOREIGN_KEY_CHECKS=1;');
echo "\n¡Proceso finalizado!\n";
