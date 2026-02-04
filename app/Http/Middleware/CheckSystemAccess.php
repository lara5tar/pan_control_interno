<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\AuthHelper;

class CheckSystemAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Permitir acceso a rutas públicas (login, logout, assets)
        $publicRoutes = [
            'login',
            'login.post',
            'logout',
            'password.*',
        ];

        // Si la ruta actual está en la lista de públicas, permitir acceso
        if ($request->routeIs($publicRoutes)) {
            return $next($request);
        }

        // Permitir acceso directo a las URLs de login/logout
        if (
            $request->is('login') || 
            $request->is('logout')
        ) {
            return $next($request);
        }

        // Permitir acceso a assets y archivos públicos
        if (
            $request->is('css/*') || 
            $request->is('js/*') || 
            $request->is('images/*') || 
            $request->is('build/*') ||
            $request->is('storage/*')
        ) {
            return $next($request);
        }

        // Verificar si el usuario tiene acceso al sistema (Admin Librería o Supervisor)
        if (!AuthHelper::canAccessSystem()) {
            // Limpiar la sesión si el usuario no tiene permisos
            session()->forget(['user', 'roles', 'congregante']);

            // Si es una petición AJAX, retornar JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'No tienes permisos para acceder al sistema. Solo Admin Librería y Supervisor pueden ingresar.'
                ], 403);
            }

            // Redirigir al login con mensaje de error
            return redirect()->route('login')
                ->withErrors(['error' => 'No tienes permisos para acceder al sistema. Solo Admin Librería y Supervisor pueden ingresar.']);
        }

        return $next($request);
    }
}
