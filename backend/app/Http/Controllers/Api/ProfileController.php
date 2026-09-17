<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'correo' => 'required|email|max:255|unique:usuarios_poa,usuario,' . $request->user()->usuario_poa_id . ',usuario_poa_id',
        ]);

        $user = $request->user();
        $user->nombre = $request->nombre;
        $user->apellido_paterno = $request->apellido;
        // Optionally clear or leave apellido_materno as is. We'll leave it to not destroy data unnecessarily.
        $user->usuario = $request->correo;
        $user->save();

        return response()->json(['message' => 'Perfil actualizado correctamente']);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada correctamente']);
    }
}
