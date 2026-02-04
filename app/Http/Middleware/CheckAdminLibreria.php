<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\AuthHelper;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminLibreria
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!AuthHelper::isAdminLibreria()) {
            // Si no es Admin Librería, redirigir al dashboard con mensaje de error
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para acceder a esta sección. Solo Admin Librería puede realizar esta acción.');
        }

        return $next($request);
    }
}
