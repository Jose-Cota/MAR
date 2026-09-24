<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$proys = DB::table('proyectos')->where('ejercicio_id', 19)->get();
foreach($proys as $p) {
    $ro17 = DB::table('responsables_operativos')->where('responsable_operativo_id', $p->responsable_operativo_id)->first();
    if($ro17 && $ro17->ejercicio_id != 19) {
        $urg17 = DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', $ro17->unidad_responsable_gasto_id)->first();
        if($urg17) {
            // Find equivalent URG in 2027
            $urg19 = DB::table('unidades_responsables_gastos')
                ->where('ejercicio_id', 19)
                ->where('nombre', $urg17->nombre)
                ->first();
            if($urg19) {
                // Find RO in 2027 with same name or same numero
                $ro19 = DB::table('responsables_operativos')
                    ->where('ejercicio_id', 19)
                    ->where('unidad_responsable_gasto_id', $urg19->unidad_responsable_gasto_id)
                    ->where('numero', $ro17->numero)
                    ->first();
                if(!$ro19) {
                    $ro19 = DB::table('responsables_operativos')
                        ->where('ejercicio_id', 19)
                        ->where('unidad_responsable_gasto_id', $urg19->unidad_responsable_gasto_id)
                        ->first();
                }
                if($ro19) {
                    DB::table('proyectos')->where('proyecto_id', $p->proyecto_id)->update(['responsable_operativo_id' => $ro19->responsable_operativo_id]);
                    echo "Fixed Proy {$p->proyecto_id}: RO {$ro17->responsable_operativo_id} -> {$ro19->responsable_operativo_id}\n";
                }
            }
        }
    }
}
