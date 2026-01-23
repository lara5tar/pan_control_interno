<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario tiene codCongregante en la sesión
        if (!Session::has('codCongregante')) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para acceder al sistema.');
        }

        // Verificar que el usuario tenga el rol de Admin Librería
        $roles = Session::get('roles', []);
        $tieneRolAdminLibreria = collect($roles)->contains(function ($rol) {
            $rolNombre = strtoupper(trim($rol['ROL'] ?? $rol['rol'] ?? ''));
            $rolId = $rol['ID'] ?? $rol['id'] ?? $rol['ROL_ID'] ?? $rol['rol_id'] ?? null;

            return $rolNombre === 'ADMIN LIBRERIA' ||
                   $rolNombre === 'ADMIN LIBRERÍA' ||
                   $rolNombre === 'SUPERVISOR' ||
                   (string) $rolId === '20';
        });
        
        if (!$tieneRolAdminLibreria) {
            // Cerrar sesión si no tiene el rol
            Session::flush();
            
            return redirect()->route('login')
                ->with('error', 'No tienes permisos para acceder al sistema. Se requiere el rol de Administrador de Librería o Supervisor.');
        }

        return $next($request);
    }
}
