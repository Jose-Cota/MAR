<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required'], // Actually expects a username, but frontend sends it as email
            'password' => ['required', 'string'],
        ]);

        $user = User::where('usuario', $credentials['email'])->first();

        \Illuminate\Support\Facades\Log::info("Login attempt", ['email' => $credentials['email'], 'password_length' => strlen($credentials['password'])]);

        if (! $user) {
            \Illuminate\Support\Facades\Log::warning("Login failed for " . $credentials['email'] . " (User not found)");
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $passwordOk = \App\Helpers\PasswordHelper::checkPassword($credentials['password'], $user->password);
        
        // Fallback for sha1 in case there are any users that are still stored with standard sha1
        // Fallback for Bcrypt (if they changed password via Profile)
        if (!$passwordOk && \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            $passwordOk = true;
        } elseif (!$passwordOk && sha1($credentials['password']) === $user->password) {
            $passwordOk = true;
        }

        if (!$passwordOk) {
            \Illuminate\Support\Facades\Log::warning("Login failed for " . $credentials['email'] . " (Invalid password)");
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Rehash-on-login: if password was verified using old Drupal algorithm or sha1, rehash to bcrypt
        if ($passwordOk && (substr($user->password, 0, 3) === '$S$' || strlen($user->password) === 40)) {
            $user->password = \Illuminate\Support\Facades\Hash::make($credentials['password']);
            $user->save();
        }

        $token = $user->createToken('poa-frontend')->plainTextToken;

        // Append spatie roles and permissions for the frontend
        $user->load('roles', 'permissions');
        $userArray = $user->toArray();
        $userArray['role'] = $user->roles->first()?->name ?? 'Usuario';
        $userArray['roles'] = $user->roles->pluck('name')->toArray();
        $userArray['permissions'] = $user->getAllPermissions()->pluck('name');

        // Check for active stages globally
        if ($userArray['role'] !== 'Administrador') {
            $activeStages = \Illuminate\Support\Facades\DB::connection('poa_prod')
                ->table('operaciones_ejercicios')
                ->where('habilitado', 'si')
                ->exists();

            if (!$activeStages) {
                \Illuminate\Support\Facades\Log::warning("Login blocked for " . $credentials['email'] . " (No active stages)");
                throw ValidationException::withMessages([
                    'email' => ['El sistema no tiene etapas activas (Elaboración o Seguimiento) en este momento. Por favor, comunícate con el Administrador.'],
                ]);
            }
        }

        return response()->json([
            'user' => $userArray,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }
}
