<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$ros = DB::connection('poa_prod')->select("SELECT status FROM proyectos WHERE proyecto_id = 1465");
echo json_encode($ros, JSON_PRETTY_PRINT);
