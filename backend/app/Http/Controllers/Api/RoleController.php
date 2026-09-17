<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles with their permissions.
     */
    public function index(Request $request)
    {
        if (!$request->user()->hasRole('Administrador')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    /**
     * Update the specified role's permissions.
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->hasRole('Administrador')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name'
        ]);

        $role = Role::findOrFail($id);
        
        // Sync permissions
        $role->syncPermissions($request->permissions);

        return response()->json([
            'message' => 'Permisos actualizados correctamente',
            'role' => $role->load('permissions')
        ]);
    }
}
