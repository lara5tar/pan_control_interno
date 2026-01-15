<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    use HasFactory;

    protected $fillable = [
        'guia',
        'fecha_envio',
        'monto_a_pagar',
        'comprobante',
        'comprobante_pago',
        'referencia_pago',
        'fecha_pago',
        'notas',
        'estado_pago',
        'tipo_generacion',
        'periodo_inicio',
        'periodo_fin',
        'usuario',
    ];

    protected $casts = [
        'fecha_envio' => 'date',
        'fecha_pago' => 'date',
        'periodo_inicio' => 'date',
        'periodo_fin' => 'date',
        'monto_a_pagar' => 'decimal:2',
    ];

    /**
     * Relación: Un envío tiene muchas ventas (muchos a muchos)
     */
    public function ventas()
    {
        return $this->belongsToMany(Venta::class, 'envio_venta')
                    ->withTimestamps();
    }

    /**
     * Scope para buscar por guía o ID
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('id', 'like', "%{$search}%")
              ->orWhere('guia', 'like', "%{$search}%");
        });
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeEntreFechas($query, $fechaDesde, $fechaHasta)
    {
        return $query->whereBetween('fecha_envio', [$fechaDesde, $fechaHasta]);
    }

    /**
     * Scope para filtrar por mes y año
     */
    public function scopePorMes($query, $mes, $anio)
    {
        return $query->whereMonth('fecha_envio', $mes)
                     ->whereYear('fecha_envio', $anio);
    }

    /**
     * Scope para filtrar envíos del día actual
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_envio', today());
    }

    /**
     * Scope para filtrar envíos de la semana actual
     */
    public function scopeSemanaActual($query)
    {
        return $query->whereBetween('fecha_envio', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope para filtrar envíos del mes actual
     */
    public function scopeMesActual($query)
    {
        return $query->whereMonth('fecha_envio', now()->month)
                     ->whereYear('fecha_envio', now()->year);
    }

    /**
     * Scope para filtrar envíos que contienen una venta específica
     */
    public function scopeConVenta($query, $ventaId)
    {
        return $query->whereHas('ventas', function($q) use ($ventaId) {
            $q->where('venta_id', $ventaId);
        });
    }

    /**
     * Calcular el total de ventas asociadas
     */
    public function calcularTotalVentas()
    {
        return $this->ventas->sum('total');
    }

    /**
     * Obtener la cantidad de ventas asociadas
     */
    public function getCantidadVentasAttribute()
    {
        return $this->ventas->count();
    }

    /**
     * Obtener el total de libros enviados (suma de cantidades de movimientos)
     */
    public function getTotalLibrosAttribute()
    {
        $total = 0;
        foreach ($this->ventas as $venta) {
            $total += $venta->movimientos->sum('cantidad');
        }
        return $total;
    }

    /**
     * Verificar si el envío está pagado
     */
    public function estaPagado()
    {
        return $this->estado_pago === 'pagado';
    }

    /**
     * Obtener el color del badge según el estado de pago
     */
    public function getBadgeColor()
    {
        return match($this->estado_pago) {
            'pagado' => 'bg-green-100 text-green-800',
            'pendiente' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Obtener el icono según el estado de pago
     */
    public function getIcon()
    {
        return match($this->estado_pago) {
            'pagado' => 'fas fa-check-circle',
            'pendiente' => 'fas fa-clock',
            default => 'fas fa-question-circle',
        };
    }

    /**
     * Obtener label del estado de pago
     */
    public function getEstadoLabel()
    {
        return match($this->estado_pago) {
            'pagado' => 'Pagado',
            'pendiente' => 'Pendiente',
            default => 'Desconocido',
        };
    }

    /**
     * Scope para filtrar por tipo de generación
     */
    public function scopeTipoGeneracion($query, $tipo)
    {
        return $query->where('tipo_generacion', $tipo);
    }

    /**
     * Scope para filtrar envíos automáticos
     */
    public function scopeAutomaticos($query)
    {
        return $query->where('tipo_generacion', 'automatico');
    }

    /**
     * Scope para filtrar envíos manuales
     */
    public function scopeManuales($query)
    {
        return $query->where('tipo_generacion', 'manual');
    }

    /**
     * Scope para filtrar por periodo específico
     */
    public function scopePorPeriodo($query, $inicio, $fin)
    {
        return $query->where('periodo_inicio', $inicio)
                     ->where('periodo_fin', $fin);
    }

    /**
     * Verificar si es un envío automático
     */
    public function esAutomatico()
    {
        return $this->tipo_generacion === 'automatico';
    }

    /**
     * Obtener el nombre del periodo
     */
    public function getNombrePeriodoAttribute()
    {
        if (!$this->periodo_inicio || !$this->periodo_fin) {
            return 'N/A';
        }

        $inicio = $this->periodo_inicio;
        $fin = $this->periodo_fin;

        if ($inicio->day <= 15) {
            return 'Primera Quincena de ' . $inicio->format('F Y');
        } else {
            return 'Segunda Quincena de ' . $inicio->format('F Y');
        }
    }
}
