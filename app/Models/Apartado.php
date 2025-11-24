<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Apartado extends Model
{
    protected $fillable = [
        'fecha_apartado',
        'descripcion',
        'estado',
        'usuario',
        'observaciones',
    ];

    protected $casts = [
        'fecha_apartado' => 'date',
    ];

    /**
     * Relación muchos a muchos con libros
     */
    public function libros(): BelongsToMany
    {
        return $this->belongsToMany(Libro::class, 'apartado_libro')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }

    /**
     * Scope para filtrar apartados activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para filtrar apartados completados
     */
    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    /**
     * Scope para filtrar apartados cancelados
     */
    public function scopeCancelados($query)
    {
        return $query->where('estado', 'cancelado');
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_apartado', $fecha);
    }

    /**
     * Obtener el label del estado
     */
    public function getEstadoLabel()
    {
        return match($this->estado) {
            'activo' => 'Activo',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado',
            default => 'Desconocido',
        };
    }

    /**
     * Obtener el color del badge según el estado
     */
    public function getBadgeColor()
    {
        return match($this->estado) {
            'activo' => 'bg-blue-100 text-blue-800',
            'completado' => 'bg-green-100 text-green-800',
            'cancelado' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Obtener el icono según el estado
     */
    public function getIcon()
    {
        return match($this->estado) {
            'activo' => 'fas fa-box-open',
            'completado' => 'fas fa-check-circle',
            'cancelado' => 'fas fa-times-circle',
            default => 'fas fa-question-circle',
        };
    }

    /**
     * Calcular el total de libros apartados
     */
    public function getTotalLibros()
    {
        return $this->libros->count();
    }

    /**
     * Calcular el total de unidades apartadas
     */
    public function getTotalUnidades()
    {
        return $this->libros->sum('pivot.cantidad');
    }

    /**
     * Activar el apartado (apartar el inventario)
     */
    public function activar()
    {
        if ($this->estado !== 'activo') {
            return false;
        }

        foreach ($this->libros as $libro) {
            $cantidad = $libro->pivot->cantidad;
            
            // Verificar que hay stock suficiente
            if ($libro->stock < $cantidad) {
                return false;
            }

            // Reducir stock disponible y aumentar stock apartado
            $libro->decrement('stock', $cantidad);
            $libro->increment('stock_apartado', $cantidad);
        }

        return true;
    }

    /**
     * Completar el apartado (se vendió todo)
     */
    public function completar()
    {
        if ($this->estado !== 'activo') {
            return false;
        }

        // Reducir el stock apartado de cada libro
        foreach ($this->libros as $libro) {
            $cantidad = $libro->pivot->cantidad;
            $libro->decrement('stock_apartado', $cantidad);
        }

        $this->estado = 'completado';
        $this->save();

        return true;
    }

    /**
     * Cancelar el apartado (devolver inventario)
     */
    public function cancelar()
    {
        if ($this->estado === 'cancelado') {
            return false;
        }

        // Si estaba activo, devolver el inventario
        if ($this->estado === 'activo') {
            foreach ($this->libros as $libro) {
                $cantidad = $libro->pivot->cantidad;
                
                // Aumentar stock disponible y reducir stock apartado
                $libro->increment('stock', $cantidad);
                $libro->decrement('stock_apartado', $cantidad);
            }
        }

        $this->estado = 'cancelado';
        $this->save();

        return true;
    }

    /**
     * Devolver parcialmente el apartado (si no se vendió todo)
     */
    public function devolverParcial($libroId, $cantidadDevolver)
    {
        if ($this->estado !== 'activo') {
            return false;
        }

        $libro = $this->libros()->where('libro_id', $libroId)->first();
        
        if (!$libro || $cantidadDevolver > $libro->pivot->cantidad) {
            return false;
        }

        // Devolver al stock y reducir el apartado
        $libro->increment('stock', $cantidadDevolver);
        $libro->decrement('stock_apartado', $cantidadDevolver);

        // Actualizar la cantidad en la tabla pivote
        $this->libros()->updateExistingPivot($libroId, [
            'cantidad' => $libro->pivot->cantidad - $cantidadDevolver
        ]);

        return true;
    }
}
