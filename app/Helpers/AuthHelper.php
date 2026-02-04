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

    /**
     * Verifica si el usuario actual es Admin Libreria (para control de vistas)
     * Solo los usuarios con rol "ADMIN LIBRERIA" pueden modificar datos
     * 
     * @return bool
     */
    public static function isAdminLibreria(): bool
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
                (string) $rolId === '20'
            ) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Verifica si el usuario tiene acceso al sistema (Admin Librería o Supervisor)
     * Solo estos roles pueden ingresar al sistema
     * 
     * @return bool
     */
    public static function canAccessSystem(): bool
    {
        $roles = session('roles', []);
        
        if (empty($roles)) {
            return false;
        }
        
        foreach ($roles as $rol) {
            $rolNombre = strtoupper(trim($rol['ROL'] ?? $rol['rol'] ?? ''));
            $rolId = $rol['ID'] ?? $rol['id'] ?? $rol['ROL_ID'] ?? $rol['rol_id'] ?? null;

            // Admin Librería
            if (
                $rolNombre === 'ADMIN LIBRERIA' ||
                $rolNombre === 'ADMIN LIBRERÍA' ||
                (string) $rolId === '20'
            ) {
                return true;
            }

            // Supervisor
            if (
                $rolNombre === 'SUPERVISOR' ||
                (string) $rolId === '19'
            ) {
                return true;
            }
        }
        
        return false;
    }
}
