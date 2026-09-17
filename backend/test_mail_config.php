<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$config = \App\Models\MailingConfig::first();
if ($config) {
    echo json_encode($config);
} else {
    echo "No config found";
}
