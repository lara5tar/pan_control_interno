<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'fecha_venta',
        'tipo_pago',
        'subtotal',
        'descuento_global',
        'total',
        'estado',
        'observaciones',
        'usuario',
    ];

    protected $casts = [
        'fecha_venta' => 'date',
        'subtotal' => 'decimal:2',
        'descuento_global' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relación: Una venta tiene muchos movimientos
     */
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }

    /**
     * Relación: Una venta pertenece a un cliente (opcional)
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Calcular el subtotal sumando todos los movimientos
     */
    public function calcularSubtotal()
    {
        return $this->movimientos->sum(function ($movimiento) {
            $precioConDescuento = $movimiento->precio_unitario;
            
            if ($movimiento->descuento) {
                $precioConDescuento -= ($movimiento->precio_unitario * $movimiento->descuento / 100);
            }
            
            return $precioConDescuento * $movimiento->cantidad;
        });
    }

    /**
     * Calcular el total aplicando descuento global
     */
    public function calcularTotal()
    {
        $subtotal = $this->calcularSubtotal();
        
        if ($this->descuento_global > 0) {
            $descuento = ($subtotal * $this->descuento_global) / 100;
            return $subtotal - $descuento;
        }
        
        return $subtotal;
    }

    /**
     * Actualizar totales de la venta
     */
    public function actualizarTotales()
    {
        $this->subtotal = $this->calcularSubtotal();
        $this->total = $this->calcularTotal();
        $this->save();
    }

    /**
     * Obtener el color del badge según el estado
     */
    public function getBadgeColor()
    {
        return match($this->estado) {
            'completada' => 'bg-green-100 text-green-800',
            'pendiente' => 'bg-yellow-100 text-yellow-800',
            'cancelada' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Obtener el icono según el estado
     */
    public function getIcon()
    {
        return match($this->estado) {
            'completada' => 'fas fa-check-circle',
            'pendiente' => 'fas fa-clock',
            'cancelada' => 'fas fa-times-circle',
            default => 'fas fa-question-circle',
        };
    }

    /**
     * Obtener label del tipo de pago
     */
    public function getTipoPagoLabel()
    {
        return match($this->tipo_pago) {
            'contado' => 'Contado',
            'credito' => 'Crédito',
            'mixto' => 'Mixto',
            default => 'Desconocido',
        };
    }

    /**
     * Obtener label del estado
     */
    public function getEstadoLabel()
    {
        return match($this->estado) {
            'completada' => 'Completada',
            'pendiente' => 'Pendiente',
            'cancelada' => 'Cancelada',
            default => 'Desconocido',
        };
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para filtrar por tipo de pago
     */
    public function scopeTipoPago($query, $tipo)
    {
        return $query->where('tipo_pago', $tipo);
    }

    /**
     * Scope para buscar por ID o cliente
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('id', 'like', "%{$search}%")
              ->orWhereHas('cliente', function($q) use ($search) {
                  $q->where('nombre', 'like', "%{$search}%");
              });
        });
    }
}
