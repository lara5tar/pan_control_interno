<?php

namespace App\Helpers;

class AuthHelper
{
    /**
     * Verifica si el usuario actual es administrador
     * 
     * @return bool
     */
    public static function isAdmin(): bool
    {
        $roles = session('roles', []);
        
        if (empty($roles)) {
            return false;
        }
        
        foreach ($roles as $rol) {
            $rolNombre = strtoupper(trim($rol['ROL'] ?? ''));
            if ($rolNombre === 'ADMIN LIBRERIA' || $rolNombre === 'ADMIN LIBRERÍA') {
                return true;
            }
        }
        
        return false;
    }
}
