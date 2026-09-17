<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

$user = DB::connection('poa_prod')->table('usuarios_poa')->whereIn('usuario_poa_id', function($q){
    $q->select('usuario_poa_id')->from('usuario_unidad_responsable')
      ->groupBy('usuario_poa_id')->havingRaw('COUNT(*) > 1');
})->first();

if(!$user) {
    echo "No users with multiple URs found.\n";
} else {
    echo "Found user: {$user->usuario} ({$user->usuario_poa_id})\n";
    $uModel = User::find($user->usuario_poa_id);
    
    $ros = DB::connection('poa_prod')->table('usuarios_responsables_operativos as uro')
        ->join('responsables_operativos as ro', 'uro.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
        ->where('uro.usuario_poa_id', $user->usuario_poa_id)
        ->select('urg.numero as urg', 'ro.numero as ro')
        ->get();
    echo "Assigned ROs:\n";
    print_r($ros->toArray());
    
    $urgs = $uModel->unidadesResponsables->pluck('numero')->toArray();
    echo "Assigned URs:\n";
    print_r($urgs);
}
