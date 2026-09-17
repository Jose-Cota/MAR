<?php

// 1. Update empty emails in usuarios_poa
$usuarios = DB::connection('poa_prod')->table('usuarios_poa')
    ->whereNull('correo')
    ->orWhere('correo', '')
    ->get();

$count = 0;
foreach ($usuarios as $u) {
    if (!empty($u->usuario)) {
        // Enforce lowercase
        $nuevoCorreo = strtolower(trim($u->usuario)) . '@tecdmx.org.mx';
        
        DB::connection('poa_prod')->table('usuarios_poa')
            ->where('usuario_poa_id', $u->usuario_poa_id)
            ->update(['correo' => $nuevoCorreo]);
        
        $count++;
    }
}

echo "Se actualizaron {$count} correos vacíos.\n\n";

// 2. Regenerate the markdown report
$ejercicio = DB::connection('poa_prod')->table('ejercicios')->where('ejercicio', '2027')->first();
if (!$ejercicio) {
    die("No se encontro el ejercicio 2027\n");
}

$proyectos = DB::connection('poa_prod')->table('proyectos as py')
    ->join('responsables_operativos as ro', 'py.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->join('subprogramas as sp', 'py.subprograma_id', '=', 'sp.subprograma_id')
    ->join('programas as pg', 'sp.programa_id', '=', 'pg.programa_id')
    ->select('py.proyecto_id', 'py.numero', 'py.nombre', 'urg.unidad_responsable_gasto_id', 'urg.numero as urg_numero', 'ro.numero as ro_numero', 'pg.numero as pg_numero', 'sp.numero as sp_numero')
    ->where('py.ejercicio_id', $ejercicio->ejercicio_id)
    ->orderBy('urg.numero')
    ->orderBy('ro.numero')
    ->orderBy('pg.numero')
    ->orderBy('sp.numero')
    ->orderBy('py.numero')
    ->get();

$md = "# Reporte de Proyectos 2027 y sus Usuarios\n\n";
$md .= "| Proyecto | Denominación | URG | Capturadores | Validadores |\n";
$md .= "|----------|--------------|-----|--------------|-------------|\n";

foreach ($proyectos as $py) {
    $usuariosData = \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Validador', 'Capturador']);
        })
        ->whereHas('unidadesResponsables', function($q) use ($py) {
            $q->where('unidades_responsables_gastos.unidad_responsable_gasto_id', $py->unidad_responsable_gasto_id);
        })
        ->with('roles')
        ->get();
        
    $capturadores = [];
    $validadores = [];
    
    foreach ($usuariosData as $u) {
        $roles = $u->roles->pluck('name')->toArray();
        $str = "{$u->usuario} (" . ($u->correo ?: 'Sin correo') . ")";
        if (in_array('Capturador', $roles)) $capturadores[] = $str;
        if (in_array('Validador', $roles)) $validadores[] = $str;
    }
    
    $clave = "{$py->urg_numero}.{$py->ro_numero}.{$py->pg_numero}.{$py->sp_numero}.{$py->numero}";
    $caps = empty($capturadores) ? 'NINGUNO' : implode('<br>', $capturadores);
    $vals = empty($validadores) ? 'NINGUNO' : implode('<br>', $validadores);
    
    $md .= "| {$clave} | {$py->nombre} | {$py->urg_numero} | {$caps} | {$vals} |\n";
}

file_put_contents('C:/Users/jose.cota/.gemini/antigravity-ide/brain/72b032da-fc7b-4f0a-ad2a-898b1a61c9c0/reporte_proyectos_usuarios_2027.md', $md);
echo "Reporte regenerado con los correos actualizados.\n";
