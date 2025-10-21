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
    ];

    protected $casts = [
        'precio' => 'double',
        'stock' => 'integer',
    ];

    // Relación con movimientos
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }
}
