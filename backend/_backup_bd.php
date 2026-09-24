<?php
ini_set('memory_limit', '512M');
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$database = env('DB_DATABASE');
$host     = env('DB_HOST');
$filename = __DIR__ . '/backups/bdMAR-23Sept2026-17hrs.sql';

echo "Iniciando respaldo de: $database\n";
echo "Destino: $filename\n\n";

$tables   = DB::select('SHOW TABLES');
$tableKey = "Tables_in_$database";

$fh = fopen($filename, 'w');
fwrite($fh, "-- Respaldo MAR: bdMAR-23Sept2026-17hrs\n");
fwrite($fh, "-- Base de datos: $database\n");
fwrite($fh, "-- Fecha: " . date('Y-m-d H:i:s') . "\n");
fwrite($fh, "-- Servidor: $host\n\n");
fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\n\n");

foreach ($tables as $tableRow) {
    $table = $tableRow->$tableKey;
    echo "  → Exportando tabla: $table ... ";

    // Estructura
    $createRow = DB::select("SHOW CREATE TABLE `$table`");
    $createSql = $createRow[0]->{'Create Table'} ?? '';
    fwrite($fh, "-- ----------------------------------------------------------\n");
    fwrite($fh, "-- Tabla: $table\n");
    fwrite($fh, "-- ----------------------------------------------------------\n");
    fwrite($fh, "DROP TABLE IF EXISTS `$table`;\n");
    fwrite($fh, $createSql . ";\n\n");

    // Contar filas
    $total = DB::table($table)->count();
    if ($total === 0) {
        fwrite($fh, "-- (sin datos)\n\n");
        echo "vacía\n";
        continue;
    }

    // Exportar en chunks de 200 filas para no saturar la memoria
    $chunkSize = 200;
    $offset    = 0;
    $exported  = 0;

    while ($offset < $total) {
        $rows = DB::table($table)->skip($offset)->take($chunkSize)->get();
        if ($rows->isEmpty()) break;

        $cols    = array_keys((array) $rows->first());
        $colList = implode('`, `', $cols);
        $inserts = [];

        foreach ($rows as $row) {
            $values = array_map(function ($v) {
                if ($v === null) return 'NULL';
                return "'" . addslashes($v) . "'";
            }, (array) $row);
            $inserts[] = '(' . implode(', ', $values) . ')';
        }

        fwrite($fh, "INSERT INTO `$table` (`$colList`) VALUES\n" . implode(",\n", $inserts) . ";\n");
        $exported += count($inserts);
        $offset   += $chunkSize;
    }

    fwrite($fh, "\n");
    echo "$exported filas\n";
}

fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
fclose($fh);

$size = round(filesize($filename) / 1024 / 1024, 2);
echo "\n✅ Respaldo completado: $filename ({$size} MB)\n";
