<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Http\Controllers\Api\POAFichasController;

class TestPOAFichasController extends POAFichasController {
    public function getFichas(Illuminate\Http\Request $request) {
        $ejercicio_id = $request->input('ejercicio_id', date('Y'));
        $area_id      = $request->input('area_id');
        // Resolver ejercicio_id numérico desde el año (ej: 2027 → 19)
        $ejercicioRow    = \Illuminate\Support\Facades\DB::table('ejercicios')->where('ejercicio', $ejercicio_id)->first();
        $ejercicio_db_id = $ejercicioRow ? $ejercicioRow->ejercicio_id : $ejercicio_id;

        $urgSeleccionada = ($area_id !== 'todas')
            ? \Illuminate\Support\Facades\DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', $area_id)->first()
            : null;

        $urgEstructuralMin = 240;
        $rgIds = null;

        $normalizar = function ($s) {
            $s = mb_strtolower(trim($s));
            $s = preg_replace('/[áàäâ]/u', 'a', $s);
            $s = preg_replace('/[éèëê]/u', 'e', $s);
            $s = preg_replace('/[íìïî]/u', 'i', $s);
            $s = preg_replace('/[óòöô]/u', 'o', $s);
            $s = preg_replace('/[úùüû]/u', 'u', $s);
            return $s;
        };

        if ($urgSeleccionada && $area_id !== 'todas') {
            $urgNombreClean = $normalizar($urgSeleccionada->nombre);
            $roIdsPoa = \Illuminate\Support\Facades\DB::table('responsables_operativos')
                ->get()
                ->filter(function ($ro) use ($normalizar, $urgNombreClean) {
                    $roNorm = $normalizar($ro->nombre);
                    if ($roNorm === $urgNombreClean) return true;
                    $urgCore = str_replace(['direccion de ', 'direccion general ', 'unidad de ', 'coordinacion de ', 'la '], '', $urgNombreClean);
                    $urgCore = str_replace('juridica', 'juridic', $urgCore);
                    $urgCore = str_replace('secretaria administrativa', 'administrativo', $urgCore);
                    return strlen($urgCore) > 5 && str_contains($roNorm, $urgCore);
                })
                ->pluck('responsable_operativo_id')
                ->toArray();
            $rgIds = array_unique($roIdsPoa);
        }

        $query = \Illuminate\Support\Facades\DB::table('proyectos')
            ->join('responsables_operativos', 'proyectos.responsable_operativo_id', '=', 'responsables_operativos.responsable_operativo_id')
            ->where('proyectos.ejercicio_id', $ejercicio_db_id)
            ->select(
                'proyectos.*',
                'proyectos.proyecto_id as id',
                'responsables_operativos.unidad_responsable_gasto_id as urg_id',
                'responsables_operativos.nombre as ro_nombre'
            );

        if ($rgIds !== null) {
            if (empty($rgIds)) {
                return response()->json([]);
            }
            $query->whereIn('proyectos.responsable_operativo_id', $rgIds);
        }

        $proyectos = $query->get();
        echo "PROYECTOS FOUND: " . count($proyectos) . "\n";
    }
}

$controller = new TestPOAFichasController();
$req = Illuminate\Http\Request::create('/api/poa/fichas', 'GET', ['ejercicio_id' => '2027', 'area_id' => '6']);
$controller->getFichas($req);
