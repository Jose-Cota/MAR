<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = ['programas', 'subprogramas', 'unidades_medidas', 'responsables_operativos'];
foreach ($tables as $t) {
    echo "--- $t ---\n";
    print_r(DB::connection('poa_prod')->getSchemaBuilder()->getColumnListing($t));
}
