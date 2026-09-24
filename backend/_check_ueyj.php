<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Proyecto;
$ps = Proyecto::whereIn('id', [1792, 1793])->get();
foreach($ps as $p) {
    echo "Project {$p->id} - Ejercicio: {$p->ejercicio} - UR: {$p->responsable_operativo_id}\n";
}
