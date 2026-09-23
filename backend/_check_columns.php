<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$columns = Illuminate\Support\Facades\Schema::getColumnListing('riesgo_indicadores');
print_r($columns);
$columns = Illuminate\Support\Facades\Schema::getColumnListing('riesgo_controles');
print_r($columns);
