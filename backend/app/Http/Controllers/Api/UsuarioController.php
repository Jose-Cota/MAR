<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Get all users
     */
    public function index(Request $request)
    {
        // Administrador puede ver a todos.
        if (!$request->user()->hasRole('Administrador')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $usuarios = User::with('roles', 'permissions', 'responsablesOperativos', 'unidadesResponsables')
            ->get()
            ->map(function ($user) {
                $userArray = $user->toArray();
                $userArray['role'] = $user->roles->first()?->name ?? 'Usuario';
                $userArray['roles'] = $user->roles->pluck('name');
                $userArray['responsables_operativos'] = $user->responsablesOperativos->map(function ($ro) {
                    $urg = DB::connection('poa_prod')->table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', $ro->unidad_responsable_gasto_id)->first();
                    $roArray = $ro->toArray();
                    $roArray['urg_numero'] = $urg ? $urg->numero : '';
                    return $roArray;
                });
                $userArray['unidades_responsables'] = $user->unidadesResponsables;
                
                // Keep for backwards compatibility in frontend if needed
                if ($user->unidadesResponsables->isNotEmpty()) {
                    $firstUr = $user->unidadesResponsables->first();
                    $userArray['area_nombre'] = $firstUr->nombre;
                    $userArray['area_numero'] = $firstUr->numero;
                    $userArray['area_id'] = $firstUr->unidad_responsable_gasto_id;
                } else {
                    $userArray['area_nombre'] = null;
                    $userArray['area_numero'] = null;
                    $userArray['area_id'] = null;
                }
                
                return $userArray;
            });

        return response()->json($usuarios);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        if (!$request->user()->hasRole('Administrador')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:255',
            'sexo' => 'nullable|string|max:1',
            'area_ids' => 'required|array|min:1',
            'area_ids.*' => 'integer',
            'usuario' => 'required|string|max:255|unique:usuarios_poa,usuario',
            'password' => 'required|string|min:6|confirmed',
            'roles_adicionales' => 'required|array',
            'roles_adicionales.*' => 'string',
            'responsables_seleccionados' => 'nullable|array',
            'responsables_seleccionados.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = new User();
        $user->nombre = $request->nombre;
        $user->apellido_paterno = $request->apellido_paterno;
        $user->apellido_materno = $request->apellido_materno ?? '';
        $user->correo = $request->correo;
        $user->sexo = $request->sexo ?? '';
        
        // We set area_id to the first element to avoid null constraints, while the pivot handles the true relation
        $user->area_id = count($request->area_ids) > 0 ? $request->area_ids[0] : 0;
        $user->usuario = $request->usuario;
        $user->password = Hash::make($request->password);
        $user->foto = '';
        $user->ejercicio_elaboracion = date('Y');
        $roles = $request->roles_adicionales;
        
        // El nivel legacy era solo Administrador o normal
        $user->nivel = in_array('Administrador', $roles) ? 'Administrador' : 'normal';
        
        // Mapeamos a banderas legacy para compatibilidad
        $user->consulta_integral = in_array('Validador', $roles) ? 'si' : 'no';
        $user->captura_seguimiento = in_array('Capturador', $roles) ? 'si' : 'no';
        
        $user->activo = 1;
        $user->save();

        $user->syncRoles($roles);
        
        if ($request->has('responsables_seleccionados')) {
            $user->responsablesOperativos()->sync($request->responsables_seleccionados);
        }

        if ($request->has('area_ids')) {
            $user->unidadesResponsables()->sync($request->area_ids);
        }

        $user->load('roles', 'permissions', 'responsablesOperativos', 'unidadesResponsables');
        $userArray = $user->toArray();
        $userArray['role'] = $user->roles->first()?->name;
        $userArray['roles'] = $user->roles->pluck('name');
        $userArray['permissions'] = $user->getAllPermissions()->pluck('name');
        $userArray['responsables_operativos'] = $user->responsablesOperativos;
        $userArray['unidades_responsables'] = $user->unidadesResponsables;

        return response()->json($userArray, 201);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->hasRole('Administrador')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:255',
            'sexo' => 'nullable|string|max:1',
            'area_ids' => 'required|array|min:1',
            'area_ids.*' => 'integer',
            'usuario' => [
                'required',
                'string',
                'max:255',
                Rule::unique('usuarios_poa')->ignore($user->usuario_poa_id, 'usuario_poa_id')
            ],
            'roles_adicionales' => 'required|array',
            'roles_adicionales.*' => 'string',
            'responsables_seleccionados' => 'nullable|array',
            'responsables_seleccionados.*' => 'integer',
            'activo' => 'nullable|boolean',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->nombre = $request->nombre;
        $user->apellido_paterno = $request->apellido_paterno;
        $user->apellido_materno = $request->apellido_materno ?? '';
        $user->correo = $request->correo;
        $user->sexo = $request->sexo ?? '';
        // Set first ID to legacy column
        $user->area_id = count($request->area_ids) > 0 ? $request->area_ids[0] : 0;
        $user->usuario = $request->usuario;
        $roles = $request->roles_adicionales;
        
        $user->nivel = in_array('Administrador', $roles) ? 'Administrador' : 'normal';
        $user->consulta_integral = in_array('Validador', $roles) ? 'si' : 'no';
        $user->captura_seguimiento = in_array('Capturador', $roles) ? 'si' : 'no';
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->has('activo')) {
            $user->activo = $request->activo ? 1 : 0;
        }
        $user->save();

        $user->syncRoles($roles);
        
        if ($request->has('responsables_seleccionados')) {
            $user->responsablesOperativos()->sync($request->responsables_seleccionados);
        }
        
        if ($request->has('area_ids')) {
            $user->unidadesResponsables()->sync($request->area_ids);
        }

        // Ya no asignamos permisos manuales porque se rigen por el rol.
        $user->syncPermissions([]);

        $user->load('roles', 'permissions', 'responsablesOperativos', 'unidadesResponsables');
        $userArray = $user->toArray();
        $userArray['role'] = $user->roles->first()?->name;
        $userArray['roles'] = $user->roles->pluck('name');
        $userArray['permissions'] = $user->getAllPermissions()->pluck('name');
        $userArray['responsables_operativos'] = $user->responsablesOperativos;
        $userArray['unidades_responsables'] = $user->unidadesResponsables;

        return response()->json($userArray);
    }

    /**
     * Change password for the specified user
     */
    public function updatePassword(Request $request, $id)
    {
        if (!$request->user()->hasRole('Administrador')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada exitosamente']);
    }

    /**
     * Toggle active status or soft-delete
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user()->hasRole('Administrador')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = User::findOrFail($id);
        
        // Alternar el estado activo/inactivo (soft deactivation)
        $user->activo = $user->activo == 1 ? 0 : 1;
        $user->save();

        return response()->json(['message' => 'Estado del usuario actualizado exitosamente', 'activo' => $user->activo]);
    }

    /**
     * Permanently delete a user
     */
    public function forceDelete(Request $request, $id)
    {
        if (!$request->user()->hasRole('Administrador')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = User::findOrFail($id);
        
        $user->unidadesResponsables()->detach();
        $user->responsablesOperativos()->detach();
        $user->roles()->detach();
        $user->permissions()->detach();
        
        $user->delete();

        return response()->json(['message' => 'Usuario eliminado permanentemente']);
    }
}
