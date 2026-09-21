<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ElaboracionController;
use App\Http\Controllers\Api\ProgramaController;
use App\Http\Controllers\Api\ProyectoController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\ResponsableOperativoController;
use App\Http\Controllers\Api\SeguimientoController;
use App\Http\Controllers\Api\SubprogramaController;
use App\Http\Controllers\Api\TableroController;
use App\Http\Controllers\Api\UnidadMedidaController;
use App\Http\Controllers\Api\UnidadResponsableController;
use App\Http\Controllers\Api\ActividadSustantivaController;
use App\Http\Controllers\Api\RiesgoController;
use App\Http\Controllers\Api\IndicadorController;
use App\Http\Controllers\Api\FichaPoaController;
use App\Http\Controllers\Api\POAFichasController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ConfiguracionController;
use App\Http\Controllers\Api\EjercicioController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'time' => now()->toDateTimeString(),
    ]);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $user->load('roles', 'permissions');
        $userArray = $user->toArray();
        $userArray['role'] = $user->roles->first()?->name ?? 'Usuario';
        $userArray['permissions'] = $user->getAllPermissions()->pluck('name');
        return $userArray;
    });
    
    Route::put('/user/profile', [ProfileController::class, 'updateProfile']);
    Route::put('/user/password', [ProfileController::class, 'updatePassword']);

    // Configuración
    // Route::post('/ejercicios', [EjercicioController::class, 'store']);
    // Route::put('/ejercicios/{id}', [EjercicioController::class, 'update']);
    // Route::post('/ejercicios/make-current/{id}', [EjercicioController::class, 'makeCurrent']);
    Route::get('/etapas-activas', [\App\Http\Controllers\Api\ModoController::class, 'getEtapasActivas']);

    // Roles y Permisos
    Route::get('/roles', [App\Http\Controllers\Api\RoleController::class, 'index']);
    Route::put('/roles/{id}', [App\Http\Controllers\Api\RoleController::class, 'update']);

    // Proyectos
    Route::get('/proyectos', [ProyectoController::class, 'index']);
    Route::post('/proyectos', [ProyectoController::class, 'store']);
    Route::get('/proyectos/{id}/ficha-descriptiva', [ProyectoController::class, 'fichaDescriptiva']);
    Route::get('/proyectos/{id}/ficha-descriptiva/pdf', [ProyectoController::class, 'fichaDescriptivaPdf']);
    Route::put('/proyectos/{id}/responsable-ficha', [ProyectoController::class, 'updateResponsableFicha']);
    Route::put('/proyectos/{id}/alineacion-pei', [ProyectoController::class, 'updateAlineacionPei']);
    Route::put('/proyectos/{id}/verificar', [ProyectoController::class, 'verificar']);
    Route::put('/proyectos/{id}/cambiar-estatus', [ProyectoController::class, 'cambiarEstatus']);
    Route::get('/proyectos/{id}/bitacora', [ProyectoController::class, 'getBitacora']);
    Route::post('/proyectos/{id}/bitacora', [ProyectoController::class, 'agregarNotaBitacora']);
    Route::delete('/proyectos/{id}', [ProyectoController::class, 'destroy']);
    Route::post('/proyectos/{id}/metas', [ProyectoController::class, 'storeMeta']);
    Route::put('/proyectos/{id}/metas/{metaId}', [ProyectoController::class, 'updateMeta']);
    Route::delete('/proyectos/{id}/metas/{metaId}', [ProyectoController::class, 'destroyMeta']);
    
    // Tablero
    Route::get('/tablero-administrativo', [TableroController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Reportes & Seguimiento
    Route::get('/reportes/seguimiento/avances', [ReporteController::class, 'seguimientoAvances']);
    Route::get('/reportes/excel/avance-trimestral', [ReporteController::class, 'descargarAvanceTrimestral']);
    Route::get('/reportes/excel/apertura-programatica', [ReporteController::class, 'descargarAperturaProgramatica']);
    Route::get('/reportes/excel/matriz-metas', [ReporteController::class, 'descargarMatrizMetas']);
    Route::get('/reportes/excel/avance-mensual/{proyecto_id}', [ReporteController::class, 'descargarAvanceMensual']);
    Route::get('/reportes/excel/indicadores', [ReporteController::class, 'descargarIndicadores']);
    Route::get('/reportes/excel/tabla-proyectos', [ReporteController::class, 'tablaProyectosExcel']);
    Route::get('/reportes/tabla-proyectos', [ReporteController::class, 'tablaProyectos']);
    Route::get('/reportes/pdf/recursos-asociados', [ReporteController::class, 'recursosAsociadosPdf']);

    // Seguimiento Captura
    Route::get('/seguimiento/metas/{proyecto_id}', [SeguimientoController::class, 'getMetasProyecto']);
    Route::put('/seguimiento/avance', [SeguimientoController::class, 'putAvanceMensual']);

    Route::get('/reportes/seguimiento/detalle-metas', [ReporteController::class, 'getDetalleMetasProyecto']);
    
    // Elaboracion
    Route::get('/elaboracion/grafica-proyectos', [ElaboracionController::class, 'getGraficaProyectos']);
    Route::get('/elaboracion/subprogramas', [ElaboracionController::class, 'getSubprogramasByPrograma']);
    Route::get('/elaboracion/proyectos', [ElaboracionController::class, 'getProyectosBySubprograma']);
    Route::get('/elaboracion/metas-proyecto', [ElaboracionController::class, 'getMetasByProyecto']);
    Route::get('/elaboracion/apertura-programatica', [ElaboracionController::class, 'getAperturaProgramatica']);
    Route::get('/elaboracion/apertura-programatica/exportar', [ElaboracionController::class, 'exportarAperturaExcel']);
    Route::get('/elaboracion/fichas-poa', [FichaPoaController::class, 'getListaFichas']);
    Route::post('/elaboracion/fichas-poa/pdf', [FichaPoaController::class, 'generarFichasPdf']);

    // POA Fichas
    Route::get('/poa/fichas', [POAFichasController::class, 'getFichas']);

    // Catalogos
    Route::get('/catalogos/ejercicios', [\App\Http\Controllers\Api\ConfiguracionController::class, 'getEjerciciosCatalog']);
    Route::apiResource('responsables-operativos', ResponsableOperativoController::class);
    Route::apiResource('programas', ProgramaController::class);
    Route::apiResource('subprogramas', SubprogramaController::class);
    Route::get('/unidades-medida/duplicates', [UnidadMedidaController::class, 'getDuplicates']);
    Route::post('/unidades-medida/migrate', [UnidadMedidaController::class, 'migrate']);
    Route::post('/unidades-medida/batch-delete', [UnidadMedidaController::class, 'batchDestroy']);
    Route::delete('/unidades-medida/{id}', [UnidadMedidaController::class, 'destroy']);

    // Configuración
    Route::middleware('role:Administrador')->group(function () {
        Route::get('/configuracion/elaboracion/{ejercicio}', [ConfiguracionController::class, 'getElaboracion']);
        Route::put('/configuracion/elaboracion/{ejercicio}', [ConfiguracionController::class, 'saveElaboracion']);
        
        Route::get('/configuracion/seguimiento/{ejercicio}', [ConfiguracionController::class, 'getSeguimiento']);
        Route::put('/configuracion/seguimiento/{ejercicio}', [ConfiguracionController::class, 'saveSeguimiento']);
        
        Route::get('/configuracion/anteproyecto/{ejercicio}', [ConfiguracionController::class, 'getAnteproyecto']);
        Route::post('/configuracion/anteproyecto/{ejercicio}', [ConfiguracionController::class, 'generarAnteproyecto']);
        
        // Mailing Config
        Route::get('/configuracion/mailing', [\App\Http\Controllers\Api\MailingConfigController::class, 'index']);
        Route::put('/configuracion/mailing', [\App\Http\Controllers\Api\MailingConfigController::class, 'update']);
        Route::post('/configuracion/mailing/test', [\App\Http\Controllers\Api\MailingConfigController::class, 'testConnection']);
        
        // Usuarios
        Route::apiResource('usuarios', UsuarioController::class);
        Route::delete('/usuarios/{usuario}/force', [UsuarioController::class, 'forceDelete']);
        Route::put('/usuarios/{usuario}/password', [UsuarioController::class, 'updatePassword']);
        
        // Modos
        Route::get('/modos', [\App\Http\Controllers\Api\ModoController::class, 'index']);
        Route::post('/modos', [\App\Http\Controllers\Api\ModoController::class, 'store']);
        Route::get('/modos/{id}', [\App\Http\Controllers\Api\ModoController::class, 'show']);
        Route::put('/modos/{id}', [\App\Http\Controllers\Api\ModoController::class, 'update']);
        Route::delete('/modos/{id}', [\App\Http\Controllers\Api\ModoController::class, 'destroy']);
    });

    Route::apiResource('unidades-medida', UnidadMedidaController::class);
    Route::apiResource('unidades-responsables', UnidadResponsableController::class);
    Route::apiResource('actividades-sustantivas', ActividadSustantivaController::class);
    Route::post('riesgos/seguimiento-mensual', [\App\Http\Controllers\Api\SeguimientoRiesgoController::class, 'seguimientoMensual']);
    Route::post('riesgos/evaluacion-trimestral', [\App\Http\Controllers\Api\SeguimientoRiesgoController::class, 'evaluacionTrimestral']);
    Route::post('riesgos/batch-validate', [RiesgoController::class, 'batchValidate']);
    Route::apiResource('riesgos', RiesgoController::class);
    Route::put('riesgos/{riesgo}/controles/{control}/validar', [RiesgoController::class, 'validarControl']);
    Route::apiResource('riesgos-institucionales', \App\Http\Controllers\Api\RiesgoInstitucionalController::class);
    Route::apiResource('indicadores', IndicadorController::class);
    Route::get('dimensiones', function () {
        return response()->json(DB::connection('poa_prod')->table('dimensiones')->get());
    });
    Route::get('frecuencias', function () {
        return response()->json(DB::connection('poa_prod')->table('frecuencias')->orderBy('orden')->get());
    });
});
