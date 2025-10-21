<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movimiento extends Model
{
    protected $fillable = [
        'libro_id',
        'tipo_movimiento',
        'tipo_entrada',
        'tipo_salida',
        'cantidad',
        'precio_unitario',
        'observaciones',
        'usuario'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relación con Libro
    public function libro(): BelongsTo
    {
        return $this->belongsTo(Libro::class);
    }

    // Tipos de entrada con etiquetas
    public static function tiposEntrada(): array
    {
        return [
            'compra' => 'Compra de Inventario',
            'devolucion' => 'Devolución de Cliente',
            'ajuste_positivo' => 'Ajuste Positivo',
            'donacion_recibida' => 'Donación Recibida'
        ];
    }

    // Tipos de salida con etiquetas
    public static function tiposSalida(): array
    {
        return [
            'venta' => 'Venta',
            'perdida' => 'Pérdida/Merma',
            'ajuste_negativo' => 'Ajuste Negativo',
            'donacion_entregada' => 'Donación Entregada',
            'prestamo' => 'Préstamo'
        ];
    }

    // Obtener etiqueta del tipo de movimiento
    public function getTipoLabel(): string
    {
        if ($this->tipo_movimiento === 'entrada') {
            return self::tiposEntrada()[$this->tipo_entrada] ?? $this->tipo_entrada;
        } else {
            return self::tiposSalida()[$this->tipo_salida] ?? $this->tipo_salida;
        }
    }

    // Obtener color del badge según el tipo
    public function getBadgeColor(): string
    {
        if ($this->tipo_movimiento === 'entrada') {
            return match($this->tipo_entrada) {
                'compra' => 'bg-green-100 text-green-800',
                'devolucion' => 'bg-blue-100 text-blue-800',
                'ajuste_positivo' => 'bg-purple-100 text-purple-800',
                'donacion_recibida' => 'bg-pink-100 text-pink-800',
                default => 'bg-gray-100 text-gray-800'
            };
        } else {
            return match($this->tipo_salida) {
                'venta' => 'bg-green-100 text-green-800',
                'perdida' => 'bg-red-100 text-red-800',
                'ajuste_negativo' => 'bg-orange-100 text-orange-800',
                'donacion_entregada' => 'bg-yellow-100 text-yellow-800',
                'prestamo' => 'bg-indigo-100 text-indigo-800',
                default => 'bg-gray-100 text-gray-800'
            };
        }
    }

    // Obtener icono según el tipo
    public function getIcon(): string
    {
        if ($this->tipo_movimiento === 'entrada') {
            return match($this->tipo_entrada) {
                'compra' => 'fas fa-shopping-cart',
                'devolucion' => 'fas fa-undo',
                'ajuste_positivo' => 'fas fa-plus-circle',
                'donacion_recibida' => 'fas fa-gift',
                default => 'fas fa-arrow-down'
            };
        } else {
            return match($this->tipo_salida) {
                'venta' => 'fas fa-cash-register',
                'perdida' => 'fas fa-exclamation-triangle',
                'ajuste_negativo' => 'fas fa-minus-circle',
                'donacion_entregada' => 'fas fa-hand-holding-heart',
                'prestamo' => 'fas fa-handshake',
                default => 'fas fa-arrow-up'
            };
        }
    }
}
