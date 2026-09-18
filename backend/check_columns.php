<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== acciones_sustantivas ===\n";
$cols = DB::select('SHOW COLUMNS FROM acciones_sustantivas');
foreach ($cols as $c) {
    echo $c->Field . ' (' . $c->Type . ")\n";
}

echo "\n=== metas ===\n";
$cols = DB::select('SHOW COLUMNS FROM metas');
foreach ($cols as $c) {
    echo $c->Field . ' (' . $c->Type . ")\n";
}

echo "\n=== indicadores ===\n";
$cols = DB::select('SHOW COLUMNS FROM indicadores');
foreach ($cols as $c) {
    echo $c->Field . ' (' . $c->Type . ")\n";
}
