<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$columns = DB::connection('poa_prod')->select('SHOW COLUMNS FROM proyectos');
print_r($columns);
