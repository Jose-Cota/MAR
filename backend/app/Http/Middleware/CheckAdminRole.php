<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $roles = $user->roles->pluck('name');
        if (!$roles->contains('Administrador') && !$roles->contains('Super Administrador')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return $next($request);
    }
}
