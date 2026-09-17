<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$query = "
    DELETE FROM usuarios_responsables_operativos 
    WHERE NOT EXISTS (
        SELECT 1 
        FROM responsables_operativos ro 
        JOIN usuario_unidad_responsable uur ON uur.unidad_responsable_gasto_id = ro.unidad_responsable_gasto_id 
        WHERE ro.responsable_operativo_id = usuarios_responsables_operativos.responsable_operativo_id 
        AND uur.usuario_poa_id = usuarios_responsables_operativos.usuario_poa_id
    )
";

$deleted = DB::connection('poa_prod')->delete($query);
echo "Removed {$deleted} orphan RO assignments that didn't belong to the user's URs.\n";
