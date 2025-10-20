<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Maneja el acceso según el rol del usuario autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles  Lista de roles permitidos
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // ============================
        // 1️⃣ Si no hay sesión activa
        // ============================
        if (!Auth::check()) {
            // Si la petición espera JSON (API o Axios)
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No autenticado. Inicia sesión para continuar.'
                ], 401);
            }

            // Si viene desde el navegador (web)
            return redirect('/login');
        }

        // ============================
        // 2️⃣ Obtener usuario autenticado
        // ============================
        $user = Auth::user();

        // ============================
        // 3️⃣ Verificar rol permitido
        // ============================
        if (!in_array($user->role, $roles)) {
            // Si es una petición JSON (API o móvil)
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No tienes permiso para acceder a esta sección.'
                ], 403);
            }

            // Si es web
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        // ============================
        // 4️⃣ Continuar con la solicitud
        // ============================
        return $next($request);
    }
}
