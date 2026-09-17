<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'usuarios_poa',
    'unidades_responsables_gastos',
    'responsables_operativos',
    'usuarios_responsables_operativos'
];

$backup = [];

foreach ($tables as $table) {
    $backup[$table] = DB::connection('poa_prod')->table($table)->get();
}

$filename = 'C:\Cota\POA\backup_usuarios_ur_ro_2026-08-20.json';
file_put_contents($filename, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Backup guardado en: " . $filename . "\n";
