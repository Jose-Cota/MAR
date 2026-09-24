<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Http\Controllers\Api\POAFichasController;

class TestPOAFichasController2 extends POAFichasController {
    public function getFichas(Illuminate\Http\Request $request) {
        $ejercicio_id = $request->input('ejercicio_id', date('Y'));
        $area_id      = $request->input('area_id');
        $ejercicioRow    = \Illuminate\Support\Facades\DB::table('ejercicios')->where('ejercicio', $ejercicio_id)->first();
        $ejercicio_db_id = $ejercicioRow ? $ejercicioRow->ejercicio_id : $ejercicio_id;
        $urgSeleccionada = ($area_id !== 'todas')
            ? \Illuminate\Support\Facades\DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', $area_id)->first()
            : null;
        $urgEstructuralMin = 240;
        $rgIds = null;
        $normalizar = function ($s) { return $s; };

        $urgNombreClean = "pleno";
        $urgEstructural = \Illuminate\Support\Facades\DB::table('unidades_responsables_gastos')
            ->where('ejercicio_id', $ejercicio_db_id)
            ->where('unidad_responsable_gasto_id', '>=', $urgEstructuralMin)
            ->where('nombre', 'Pleno')
            ->first();

        $roIdsPoa = \Illuminate\Support\Facades\DB::table('responsables_operativos')
            ->where('ejercicio_id', $ejercicio_db_id)
            ->where('unidad_responsable_gasto_id', $urgEstructural->unidad_responsable_gasto_id)
            ->pluck('responsable_operativo_id')
            ->toArray();
        $rgIds = array_unique($roIdsPoa);

        $query = \Illuminate\Support\Facades\DB::table('proyectos')
            ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
            ->where('proyectos.ejercicio_id', $ejercicio_db_id);
        $query->whereIn('proyectos.responsable_operativo_id', $rgIds);

        $proyectos = $query->get();
        echo "PROYECTOS FOUND: " . count($proyectos) . "\n";
    }
}

$controller = new TestPOAFichasController2();
$req = Illuminate\Http\Request::create('/api/poa/fichas', 'GET', ['ejercicio_id' => '2027', 'area_id' => '1']);
$controller->getFichas($req);
