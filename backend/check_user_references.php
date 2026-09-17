<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find duplicates (using base username)
$users = DB::connection('poa_prod')->table('usuarios_poa')->get();

$groups = [];
foreach($users as $user) {
    $baseUsername = preg_replace('/[_\-\d].*$/', '', strtolower($user->usuario));
    if (!isset($groups[$baseUsername])) {
        $groups[$baseUsername] = [];
    }
    $groups[$baseUsername][] = $user;
}

$duplicates = array_filter($groups, function($g) { return count($g) > 1 && count($g) < 6; });

$duplicateIds = [];
foreach($duplicates as $group) {
    // skip the first one (assume it's the main one)
    for($i = 1; $i < count($group); $i++) {
        $duplicateIds[] = $group[$i]->usuario_poa_id;
    }
}

echo "Total duplicate IDs to potentially delete: " . count($duplicateIds) . "\n";

if (count($duplicateIds) > 0) {
    // Check if any of these IDs are in proyecto_bitacoras
    $bitacorasCount = DB::connection('poa_prod')->table('proyecto_bitacoras')
        ->whereIn('user_id', $duplicateIds)
        ->count();
    
    echo "Registros en 'proyecto_bitacoras' ligados a duplicados: " . $bitacorasCount . "\n";
    
    // Are there any other tables? Let's check information_schema for foreign keys targeting usuarios_poa
    $fks = DB::connection('poa_prod')->select("
        SELECT TABLE_NAME, COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
          AND REFERENCED_TABLE_NAME = 'usuarios_poa';
    ");
    
    echo "Tablas con Foreign Keys hacia usuarios_poa:\n";
    foreach($fks as $fk) {
        echo "- {$fk->TABLE_NAME}.{$fk->COLUMN_NAME}\n";
        
        $count = DB::connection('poa_prod')->table($fk->TABLE_NAME)
            ->whereIn($fk->COLUMN_NAME, $duplicateIds)
            ->count();
        echo "  -> Registros ligados a los duplicados: " . $count . "\n";
    }
}
