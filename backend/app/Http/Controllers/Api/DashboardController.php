<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function applyAccessScope($query, ?\App\Models\User $user)
    {
        $isAdministrador = $user && ($user->hasRole('Administrador') || $user->hasRole('Administrador', 'web') || $user->hasRole('DPyRF') || $user->roles->pluck('name')->contains('Administrador') || $user->roles->pluck('name')->contains('DPyRF'));

        if ($user && !$isAdministrador) {
            $query->where(function($q) use ($user) {
                $userUrgNumbers = $user->unidadesResponsables->pluck('numero')->toArray();
                if (empty($userUrgNumbers)) {
                    $userUrgNumbers = DB::connection('poa_prod')
                        ->table('unidades_responsables_gastos')
                        ->where('unidad_responsable_gasto_id', $user->area_id)
                        ->pluck('numero')->toArray();
                }

                $q->whereIn('urg.numero', $userUrgNumbers)
                  ->whereExists(function($subquery) use ($user) {
                      $subquery->select(DB::raw(1))
                               ->from('usuarios_responsables_operativos as uro')
                               ->whereColumn('ro.responsable_operativo_id', 'uro.responsable_operativo_id') 
                               ->where('uro.usuario_poa_id', $user->usuario_poa_id);
                  });
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $ejercicio = $request->query('ejercicio');

        $ejercicioId = null;
        if ($ejercicio) {
            $ejercicioId = DB::connection('poa_prod')
                ->table('ejercicios')
                ->where('ejercicio', $ejercicio)
                ->value('ejercicio_id');
        }

        // 1. Total proyectos y URGs
        $qProyectos = DB::connection('poa_prod')
            ->table('proyectos as p')
            ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->when($ejercicioId, fn($q) => $q->where('urg.ejercicio_id', $ejercicioId));
            
        $qProyectos = $this->applyAccessScope($qProyectos, $user);

        $totalProyectos = (clone $qProyectos)->count('p.proyecto_id');
        $totalUrgs = (clone $qProyectos)->distinct()->count('urg.unidad_responsable_gasto_id');

        // 2. Proyectos en Captura
        $qCaptura = DB::connection('poa_prod')
            ->table('proyectos as p')
            ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->when($ejercicioId, fn($q) => $q->where('urg.ejercicio_id', $ejercicioId))
            ->where(function($q) {
                $q->whereNull('p.status')
                  ->orWhereIn(DB::raw('LOWER(p.status)'), ['0', 'abierto', 'captura', 'capturado']);
            });
            
        $qCaptura = $this->applyAccessScope($qCaptura, $user);
        $proyectosCaptura = $qCaptura->count('p.proyecto_id');

        // 3. Proyectos en Validacion
        $qValidacion = DB::connection('poa_prod')
            ->table('proyectos as p')
            ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->when($ejercicioId, fn($q) => $q->where('urg.ejercicio_id', $ejercicioId))
            ->whereIn(DB::raw('LOWER(p.status)'), ['validacion', 'validación', 'validado', 'enviada', 'enviado']);
            
        $qValidacion = $this->applyAccessScope($qValidacion, $user);
        $proyectosValidacion = $qValidacion->count('p.proyecto_id');

        // 4. Proyectos Cerrados
        $qCerrados = DB::connection('poa_prod')
            ->table('proyectos as p')
            ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->when($ejercicioId, fn($q) => $q->where('urg.ejercicio_id', $ejercicioId))
            ->whereIn(DB::raw('LOWER(p.status)'), ['cerrada', 'cerrado', 'dpyrf']);
            
        $qCerrados = $this->applyAccessScope($qCerrados, $user);
        $proyectosCerrados = $qCerrados->count('p.proyecto_id');

        // 4.5 Proyectos Verificados
        $qVerificados = DB::connection('poa_prod')
            ->table('proyectos as p')
            ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
            ->join('unidades_responsables_gastos as urg', 'ro.unidad_responsable_gasto_id', '=', 'urg.unidad_responsable_gasto_id')
            ->when($ejercicioId, fn($q) => $q->where('urg.ejercicio_id', $ejercicioId))
            ->whereIn(DB::raw('LOWER(p.status)'), ['verificado']);
            
        $qVerificados = $this->applyAccessScope($qVerificados, $user);
        $proyectosVerificados = $qVerificados->count('p.proyecto_id');

        // 5. Fichas por URG
        // We can't applyAccessScope directly to urg list if it filters by RO because 'ro' table is not joined here.
        // Instead we let applyAccessScope handle the project queries, and just fetch URGs that belong to the user.
        $qUrgs = DB::connection('poa_prod')
            ->table('unidades_responsables_gastos as urg')
            ->select('urg.unidad_responsable_gasto_id','urg.numero','urg.nombre')
            ->when($ejercicioId, fn($q) => $q->where('urg.ejercicio_id', $ejercicioId))
            ->orderBy('urg.numero');
        $isAdministradorGlobal = $user && ($user->hasRole('Administrador') || $user->hasRole('Administrador', 'web') || $user->hasRole('DPyRF') || $user->roles->pluck('name')->contains('Administrador') || $user->roles->pluck('name')->contains('DPyRF'));
        if ($user && !$isAdministradorGlobal) {
            $userUrgNumbers = $user->unidadesResponsables->pluck('numero')->toArray();
            if (empty($userUrgNumbers)) {
                $userUrgNumbers = DB::connection('poa_prod')
                        ->table('unidades_responsables_gastos')
                        ->where('unidad_responsable_gasto_id', $user->area_id)
                        ->pluck('numero')->toArray();
            }
            $qUrgs->whereIn('urg.numero', $userUrgNumbers);
        }
            

        $urgs = $qUrgs->get();

        $fichasPorUrg = [];
        foreach ($urgs as $urg) {
            $qProyectosUrg = DB::connection('poa_prod')
                ->table('proyectos as p')
                ->join('responsables_operativos as ro','p.responsable_operativo_id','=','ro.responsable_operativo_id')
                ->join('unidades_responsables_gastos as urg','ro.unidad_responsable_gasto_id','=','urg.unidad_responsable_gasto_id')
                ->where('urg.unidad_responsable_gasto_id', $urg->unidad_responsable_gasto_id);
                
            $proyectosUrg = $this->applyAccessScope($qProyectosUrg, $user)->get(['p.proyecto_id','p.numero','p.nombre','p.status', 'ro.numero as ro_numero', 'ro.nombre as ro_nombre']);

            if ($proyectosUrg->isEmpty()) continue;

            $totalProy = $proyectosUrg->count();
            $completos = 0;
            $proyectosList = [];
            
            foreach ($proyectosUrg as $proj) {
                $ok = DB::connection('poa_prod')->table('metas')->where('proyecto_id',$proj->proyecto_id)->where('tipo','principal')->exists()
                    && DB::connection('poa_prod')->table('metas')->where('proyecto_id',$proj->proyecto_id)->where('tipo','complementaria')->exists()
                    && DB::connection('poa_prod')->table('indicadores as i')->join('metas as m','i.meta_id','=','m.meta_id')->where('m.proyecto_id',$proj->proyecto_id)->exists();
                
                if ($ok) $completos++;
                
                $proyectosList[] = [
                    'proyecto_id' => $proj->proyecto_id,
                    'numero' => $proj->numero,
                    'nombre' => $proj->nombre,
                    'estatus' => strtolower($proj->status ?? 'captura'),
                    'ro_numero' => $proj->ro_numero,
                    'ro_nombre' => $proj->ro_nombre,
                    'avance' => $ok ? 100 : 0
                ];
            }

            $estatusCounts = $proyectosUrg->groupBy(fn($p) => strtolower($p->status ?? 'captura'));
            $estadoDominante = $estatusCounts->sortByDesc(fn($g) => $g->count())->keys()->first() ?? 'captura';

            $fichasPorUrg[] = [
                'urg_id'    => $urg->unidad_responsable_gasto_id,
                'numero'    => $urg->numero,
                'nombre'    => $urg->nombre,
                'proyectos' => $totalProy,
                'avance'    => $totalProy > 0 ? round(($completos / $totalProy) * 100) : 0,
                'estatus'   => $estadoDominante,
                'proyectosList' => $proyectosList
            ];
        }

        return response()->json([
            'total_proyectos'      => $totalProyectos,
            'total_urgs'           => $totalUrgs,
            'proyectos_captura'    => $proyectosCaptura,
            'proyectos_validacion' => $proyectosValidacion,
            'proyectos_verificados'=> $proyectosVerificados,
            'proyectos_cerrados'   => $proyectosCerrados,
            'fichas_por_urg'       => $fichasPorUrg,
            'updated_at'           => now()->format('d/m/Y H:i'),
        ]);
    }
}
