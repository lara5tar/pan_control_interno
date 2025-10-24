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

        return $next($request);
    }
}
