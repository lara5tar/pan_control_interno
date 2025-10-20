<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
