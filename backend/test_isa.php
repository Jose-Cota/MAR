<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = DB::connection('poa_prod')->table('usuarios_poa')->where('nombre', 'LIKE', '%Isa%')->select('nombre', 'apellido_paterno', 'apellido_materno')->get();
echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
