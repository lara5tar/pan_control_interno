<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Libro extends Model
{
    protected $table = 'libros';

    protected $fillable = [
        'nombre',
        'codigo_barras',
        'precio',
        'stock',
        'stock_apartado',
    ];

    protected $casts = [
        'precio' => 'double',
        'stock' => 'integer',
        'stock_apartado' => 'integer',
    ];

    // Relación con movimientos
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    /**
     * Relación con apartados
     */
    public function apartados()
    {
        return $this->belongsToMany(Apartado::class, 'apartado_libro')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }

    /**
     * Obtener el stock disponible (stock - stock_apartado)
     */
    public function getStockDisponibleAttribute()
    {
        return $this->stock - ($this->stock_apartado ?? 0);
    }
}
