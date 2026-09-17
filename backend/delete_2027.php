<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ejId = 18; // 2027

// Get programs
$progs = DB::connection('poa_prod')->table('programas')->where('ejercicio_id', $ejId)->pluck('programa_id')->all();
if (!empty($progs)) {
    DB::connection('poa_prod')->table('subprogramas')->whereIn('programa_id', $progs)->delete();
    DB::connection('poa_prod')->table('programas')->whereIn('programa_id', $progs)->delete();
}

// Get URGs
$urgs = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('ejercicio_id', $ejId)->pluck('unidad_responsable_gasto_id')->all();
if (!empty($urgs)) {
    $ros = DB::connection('poa_prod')->table('responsables_operativos')->whereIn('unidad_responsable_gasto_id', $urgs)->pluck('responsable_operativo_id')->all();
    if (!empty($ros)) {
        $pys = DB::connection('poa_prod')->table('proyectos')->whereIn('responsable_operativo_id', $ros)->pluck('proyecto_id')->all();
        if (!empty($pys)) {
            $metas = DB::connection('poa_prod')->table('metas')->whereIn('proyecto_id', $pys)->pluck('meta_id')->all();
            if (!empty($metas)) {
                DB::connection('poa_prod')->table('meses_metas_programadas')->whereIn('meta_id', $metas)->delete();
                DB::connection('poa_prod')->table('indicadores')->whereIn('meta_id', $metas)->delete();
            }
            DB::connection('poa_prod')->table('metas')->whereIn('proyecto_id', $pys)->delete();
            DB::connection('poa_prod')->table('actividades_sustantivas')->whereIn('proyecto_id', $pys)->delete();
            DB::connection('poa_prod')->table('pei_proyecto_alineaciones')->whereIn('proyecto_id', $pys)->delete();
            DB::connection('poa_prod')->table('proyectos')->whereIn('proyecto_id', $pys)->delete();
        }
        DB::connection('poa_prod')->table('responsables_operativos')->whereIn('responsable_operativo_id', $ros)->delete();
    }
    DB::connection('poa_prod')->table('unidades_responsables_gastos')->whereIn('unidad_responsable_gasto_id', $urgs)->delete();
}

DB::connection('poa_prod')->table('ejercicios')->where('ejercicio_id', $ejId)->delete();
echo "Deleted 2027 successfully.\n";
