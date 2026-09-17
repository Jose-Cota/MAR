<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$u = DB::connection('poa_prod')->table('usuarios_poa')->where('usuario', 'analuisa.oliver')->first();
if($u) { 
    $ros = DB::connection('poa_prod')->table('usuarios_responsables_operativos')->where('usuario_poa_id', $u->usuario_poa_id)->get(); 
    echo json_encode($ros); 
}
