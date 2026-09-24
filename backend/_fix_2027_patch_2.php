<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$areas_patch = [
    'Dirección de Planeación y Recursos Financieros' => [
        ['numero' => '1', 'descripcion' => 'Planeación, integración del POA y Anteproyecto de Presupuesto'],
        ['numero' => '2', 'descripcion' => 'Informes programático-presupuestales, financieros y Cuenta Pública'],
        ['numero' => '3', 'descripcion' => 'Gestión, control y seguimiento presupuestal'],
        ['numero' => '4', 'descripcion' => 'Registro, control y armonización contable'],
        ['numero' => '5', 'descripcion' => 'Conciliaciones y control de recursos financieros'],
    ],
    'Recursos Humanos' => [
        ['numero' => '1', 'descripcion' => 'Nómina, remuneraciones y pagos'],
        ['numero' => '2', 'descripcion' => 'Movimientos y seguridad social'],
        ['numero' => '3', 'descripcion' => 'Salud y seguros del personal'],
        ['numero' => '4', 'descripcion' => 'Normatividad, procedimientos y servicios al personal'],
    ],
    'Recursos Materiales' => [
        ['numero' => '1', 'descripcion' => 'Programa y procedimientos de adquisiciones'],
        ['numero' => '2', 'descripcion' => 'Servicios generales y contratación de servicios institucionales'],
        ['numero' => '3', 'descripcion' => 'Protección civil y capacitación de brigadas'],
        ['numero' => '4', 'descripcion' => 'Transparencia, solicitudes de información y atención de auditorías'],
    ]
];

$proyectos_2027 = DB::connection('poa_prod')->table('proyectos as p')
    ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
    ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
    ->where('p.ejercicio_id', 19)
    ->select('p.proyecto_id', 'urg.nombre')
    ->get();

foreach($areas_patch as $area => $actividades) {
    $matched_proyectos = [];
    foreach($proyectos_2027 as $p) {
        if(stripos($p->nombre, $area) !== false) {
            $matched_proyectos[] = $p;
        }
    }
    
    if(count($matched_proyectos) == 0) {
        echo "NO SE ENCONTRÓ PROYECTO 2027 PARA $area\n";
        continue;
    }
    
    foreach($matched_proyectos as $p) {
        DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $p->proyecto_id)->delete();
        DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $p->proyecto_id)->delete();
        
        foreach($actividades as $act) {
            DB::connection('poa_prod')->table('actividades_sustantivas')->insert([
                'proyecto_id' => $p->proyecto_id,
                'numero' => $act['numero'],
                'descripcion' => $act['descripcion'],
                'recursos_asociados' => '',
                'es_resumida' => 1
            ]);
            
            DB::connection('poa_prod')->table('acciones_sustantivas')->insert([
                'proyecto_id' => $p->proyecto_id,
                'numero' => $act['numero'],
                'descripcion' => $act['descripcion'],
                'recursos_asociados' => '',
                'es_resumida' => 1
            ]);
        }
        echo "Actualizado Proyecto {$p->proyecto_id} para {$p->nombre}\n";
    }
}
echo "Done 2027 part 2.\n";
