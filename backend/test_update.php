<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$act = App\Models\ActividadSustantiva::first();
if ($act) {
    echo "Original: " . $act->descripcion . "\n";
    $act->update(['descripcion' => $act->descripcion . ' test']);
    echo "Updated: " . $act->fresh()->descripcion . "\n";
    // revert
    $act->update(['descripcion' => str_replace(' test', '', $act->descripcion)]);
} else {
    echo "No act found\n";
}
