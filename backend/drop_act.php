<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
Schema::dropIfExists('actividades_sustantivas');
DB::table('migrations')->where('migration', 'like', '%actividad_sustantivas%')->delete();
echo 'Dropped';
