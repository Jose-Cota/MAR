<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Table actividades_sustantivas exists: " . (Schema::hasTable('actividades_sustantivas') ? 'Yes' : 'No') . "\n";
if (Schema::hasTable('actividades_sustantivas')) {
    echo "Columns:\n";
    print_r(Schema::getColumnListing('actividades_sustantivas'));
}

echo "Table acciones_sustantivas exists: " . (Schema::hasTable('acciones_sustantivas') ? 'Yes' : 'No') . "\n";
if (Schema::hasTable('acciones_sustantivas')) {
    echo "Columns:\n";
    print_r(Schema::getColumnListing('acciones_sustantivas'));
}
