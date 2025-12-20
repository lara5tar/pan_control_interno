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
        'stock_subinventario',
    ];

    protected $casts = [
        'precio' => 'double',
        'stock' => 'integer',
        'stock_subinventario' => 'integer',
    ];

    // Relación con movimientos
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    /**
     * Relación con sub-inventarios
     */
    public function subinventarios()
    {
        return $this->belongsToMany(SubInventario::class, 'subinventario_libro')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }

    /**
     * Obtener el stock total (inventario general + subinventarios)
     */
    public function getStockTotalAttribute()
    {
        return $this->stock + ($this->stock_subinventario ?? 0);
    }
}
