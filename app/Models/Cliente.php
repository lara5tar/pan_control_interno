<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nombre',
        'telefono'
    ];

    /**
     * Relación: Un cliente puede tener muchas ventas
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    /**
     * Obtener el total de ventas del cliente
     */
    public function getTotalVentasAttribute()
    {
        return $this->ventas()->sum('total');
    }

    /**
     * Obtener el número de ventas del cliente
     */
    public function getNumeroVentasAttribute()
    {
        return $this->ventas()->count();
    }

    /**
     * Obtener ventas pendientes del cliente
     */
    public function ventasPendientes()
    {
        return $this->ventas()->where('estado', 'pendiente');
    }

    /**
     * Obtener el total adeudado (ventas pendientes)
     */
    public function getTotalAdeudadoAttribute()
    {
        return $this->ventasPendientes()->sum('total');
    }
}
