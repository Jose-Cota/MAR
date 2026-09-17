<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "Iniciando proceso de consolidación...\n";

// 1. RESPALDO
$timestamp = date('Ymd_His');
echo "1. Creando tablas de respaldo (usuarios_poa_bak_{$timestamp} y model_has_roles_bak_{$timestamp})...\n";

DB::connection('poa_prod')->statement("CREATE TABLE usuarios_poa_bak_{$timestamp} AS SELECT * FROM usuarios_poa");
DB::connection('poa_prod')->statement("CREATE TABLE model_has_roles_bak_{$timestamp} AS SELECT * FROM model_has_roles");
DB::connection('poa_prod')->statement("CREATE TABLE usuario_unidad_responsable_bak_{$timestamp} AS SELECT * FROM usuario_unidad_responsable");
DB::connection('poa_prod')->statement("CREATE TABLE usuarios_responsables_operativos_bak_{$timestamp} AS SELECT * FROM usuarios_responsables_operativos");

echo "   Respaldos creados con éxito.\n";

// 2. CONSOLIDACIÓN
$users = DB::connection('poa_prod')->table('usuarios_poa')->orderBy('usuario_poa_id', 'asc')->get();

$groups = [];
foreach($users as $user) {
    // Agrupar por nombre de usuario base (removiendo sufijos como _ur2, .01, etc)
    // Cuidado: .01 en "vacante.01" puede ser necesario, pero como detectamos duplicados, lo agrupamos.
    $baseUsername = preg_replace('/[_\-\d].*$/', '', strtolower($user->usuario));
    
    // Exception for "vacante" so we don't merge different vacant accounts across the board if they are fundamentally different, 
    // but the user's data showed vacante.01 duplicated. Let's group them by exact same base username.
    if (!isset($groups[$baseUsername])) {
        $groups[$baseUsername] = [];
    }
    $groups[$baseUsername][] = $user;
}

$duplicates = array_filter($groups, function($g) { return count($g) > 1 && count($g) < 6; });

$consolidatedCount = 0;
$deletedCount = 0;

foreach($duplicates as $base => $group) {
    // El primer registro (más antiguo) será el principal, a menos que uno tenga el username exacto a la base
    $mainUser = $group[0];
    foreach($group as $u) {
        if (strtolower($u->usuario) === $base) {
            $mainUser = $u;
            break;
        }
    }
    
    $mainUserId = $mainUser->usuario_poa_id;
    $allUrgIds = [];
    $allRoIds = [];
    $duplicateIds = [];
    
    // Recolectar URGs y ROs de todos
    foreach($group as $u) {
        if ($u->area_id) {
            $allUrgIds[] = $u->area_id;
        }
        
        // Obtener ROs si hay
        $ros = DB::connection('poa_prod')->table('usuarios_responsables_operativos')
            ->where('usuario_poa_id', $u->usuario_poa_id)->pluck('responsable_operativo_id')->toArray();
        $allRoIds = array_merge($allRoIds, $ros);
        
        if ($u->usuario_poa_id !== $mainUserId) {
            $duplicateIds[] = $u->usuario_poa_id;
        }
    }
    
    $allUrgIds = array_unique(array_filter($allUrgIds));
    $allRoIds = array_unique(array_filter($allRoIds));
    
    echo "Consolidando grupo [{$base}] -> Cuenta Principal ID: {$mainUserId}\n";
    
    // Asignar todas las URG al main user
    $userModel = User::find($mainUserId);
    if ($userModel) {
        $userModel->unidadesResponsables()->syncWithoutDetaching($allUrgIds);
        $userModel->responsablesOperativos()->syncWithoutDetaching($allRoIds);
    }
    
    // Eliminar los duplicados
    foreach($duplicateIds as $dupId) {
        $dupModel = User::find($dupId);
        if ($dupModel) {
            $dupModel->unidadesResponsables()->detach();
            $dupModel->responsablesOperativos()->detach();
            $dupModel->roles()->detach();
            $dupModel->permissions()->detach();
            $dupModel->delete();
            $deletedCount++;
            echo "  - Eliminada cuenta duplicada ID: {$dupId}\n";
        }
    }
    $consolidatedCount++;
}

echo "==========================================\n";
echo "Consolidación terminada.\n";
echo "Grupos consolidados: {$consolidatedCount}\n";
echo "Cuentas duplicadas eliminadas: {$deletedCount}\n";
echo "==========================================\n";

