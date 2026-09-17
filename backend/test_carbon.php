<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Carbon\Carbon::setLocale('es');
$fecha = now()->translatedFormat('d \d\e F \d\e Y H:i \h\r\s');
echo "Output: $fecha\n";
