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
            $rolNombre = strtoupper(trim($rol['ROL'] ?? $rol['rol'] ?? ''));
            $rolId = $rol['ID'] ?? $rol['id'] ?? $rol['ROL_ID'] ?? $rol['rol_id'] ?? null;

            if (
                $rolNombre === 'ADMIN LIBRERIA' ||
                $rolNombre === 'ADMIN LIBRERÍA' ||
                $rolNombre === 'SUPERVISOR' ||
                (string) $rolId === '20'
            ) {
                return true;
            }
        }
        
        return false;
    }
}
