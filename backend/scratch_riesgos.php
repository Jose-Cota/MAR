<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$riesgos = \DB::select('SELECT id, area_id, status FROM riesgos LIMIT 5;');
print_r($riesgos);
