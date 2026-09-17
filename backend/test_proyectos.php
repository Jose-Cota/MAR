<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = ['analuisa.oliver', 'admin@tecdmx.gob.mx', 'isai.fararoni', 'jose.cota', 'aristides.guerrero'];

foreach ($users as $username) {
    $user = App\Models\User::where('usuario', $username)->first();
    if (!$user) {
        echo "$username not found\n";
        continue;
    }
    
    $isAdministrador = $user && ($user->hasRole('Administrador') || $user->hasRole('Administrador', 'web') || $user->roles->pluck('name')->contains('Administrador'));
    
    $uroCount = \Illuminate\Support\Facades\DB::table('usuarios_responsables_operativos')
        ->where('usuario_poa_id', $user->usuario_poa_id)->count();

    $query = \Illuminate\Support\Facades\DB::table('proyectos as py')
            ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
            ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
            ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id');

    if (!$isAdministrador) {
        $query->join('usuarios_responsables_operativos as uro', function($join) use ($user) {
            $join->on('ro.responsable_operativo_id', '=', 'uro.responsable_operativo_id')
                 ->where('uro.usuario_poa_id', '=', $user->usuario_poa_id);
        });
    }

    echo "$username (URG: {$user->area_id}, Nivel: {$user->nivel}, Admin: ".($isAdministrador?'Yes':'No').", URO maps: $uroCount) -> Proyectos: " . $query->count() . "\n";
}
