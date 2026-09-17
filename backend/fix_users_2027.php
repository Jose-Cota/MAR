<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get 2026 and 2027 ROs
$ros2027 = DB::connection('poa_prod')->table('responsables_operativos as ro')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->where('urg.ejercicio_id', 19) // 2027
    ->select('ro.responsable_operativo_id', 'ro.numero', 'urg.numero as urg_numero')
    ->get();

$ros2026 = DB::connection('poa_prod')->table('responsables_operativos as ro')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->where('urg.ejercicio_id', 17) // 2026
    ->select('ro.responsable_operativo_id', 'ro.numero', 'urg.numero as urg_numero')
    ->get();

$count = 0;
foreach ($ros2027 as $ro27) {
    // find matching ro26
    $ro26 = $ros2026->first(function($item) use ($ro27) {
        return $item->numero === $ro27->numero && $item->urg_numero === $ro27->urg_numero;
    });

    if ($ro26) {
        $assignments = DB::connection('poa_prod')->table('usuarios_responsables_operativos')
            ->where('responsable_operativo_id', $ro26->responsable_operativo_id)
            ->get();
        
        foreach ($assignments as $a) {
            $exists = DB::connection('poa_prod')->table('usuarios_responsables_operativos')
                ->where('responsable_operativo_id', $ro27->responsable_operativo_id)
                ->where('usuario_poa_id', $a->usuario_poa_id)
                ->exists();
            if (!$exists) {
                DB::connection('poa_prod')->table('usuarios_responsables_operativos')->insert([
                    'responsable_operativo_id' => $ro27->responsable_operativo_id,
                    'usuario_poa_id' => $a->usuario_poa_id
                ]);
                $count++;
            }
        }
    }
}
echo "Assigned $count users to 2027 ROs.\n";
