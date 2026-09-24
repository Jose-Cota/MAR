<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tablesToDrop = [
    'acciones_complementarias_derechos_humanos',
    'acciones_sustantivas_derechos_humanos',
    'capitulos_derechos_humanos',
    'lineas_acciones_derechos_humanos',
    'meses_controles_derechos_humanos',
    'meses_derechos_humanos_alcanzados',
    'meses_derechos_humanos_programados',
    'nucleos_derechos_humanos',
    'programas_derechos_humanos',
    'subcapitulos_derechos_humanos',
    'tipos_lineas_derechos_humanos',
    'unidades_derechos_humanos',
    'unidades_medidas_derechos_humanos'
];

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

foreach ($tablesToDrop as $table) {
    if (Schema::hasTable($table)) {
        Schema::drop($table);
        echo "Tabla eliminada: $table\n";
    } else {
        echo "La tabla no existe: $table\n";
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=1;');
echo "\n¡Proceso finalizado!\n";
