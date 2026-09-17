<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
DB::connection('poa_prod')->table('metas')->whereNotNull('meta_padre_id')->update(['meta_padre_id' => null]);
echo 'Reverted parents';
