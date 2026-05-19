<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['mensaje' => 'No autenticado'], 401);
        }

        // Asumiendo que role_id = 1 es admin, role_id = 2 es mesero.
        // Si tu lógica de roles es diferente, ajustaremos esto.
        $roleIdRequerido = ($role === 'admin') ? 1 : 2;

        if ($user->role_id != $roleIdRequerido && $user->role_id != 1) { 
            // El admin (1) puede acceder a todo si quieres, pero para ser estrictos con la separación:
            if ($user->role_id != $roleIdRequerido) {
                return response()->json(['mensaje' => 'Acceso denegado. No tienes los permisos necesarios.'], 403);
            }
        }

        return $next($request);
    }
}
