<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Venta;
use App\Models\Libro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    /**
     * Mostrar formulario para crear un nuevo pago
     */
    public function create(Venta $venta)
    {
        // Verificar que sea venta a plazos
        if (!$venta->es_a_plazos) {
            return redirect()->route('ventas.show', $venta)
                ->with('warning', 'Esta no es una venta a plazos');
        }

        // Verificar que no esté cancelada
        if ($venta->estado === 'cancelada') {
            return redirect()->route('ventas.show', $venta)
                ->with('error', 'No se pueden registrar pagos en una venta cancelada');
        }

        // Verificar que no esté completamente pagada
        if ($venta->estado_pago === 'completado') {
            return redirect()->route('ventas.show', $venta)
                ->with('info', 'Esta venta ya está completamente pagada');
        }

        $venta->load(['cliente', 'pagos']);
        
        return view('ventas.pago', compact('venta'));
    }

    /**
     * Listar ventas a plazos
     */
    public function index(Request $request)
    {
        $query = Venta::with(['cliente', 'pagos'])
            ->ventasAPlazo();

        // Filtrar por estado de pago
        if ($request->has('estado_pago') && $request->estado_pago != '') {
            $query->where('estado_pago', $request->estado_pago);
        }

        // Búsqueda
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        switch ($request->get('ordenar', 'reciente')) {
            case 'reciente':
                $query->latest();
                break;
            case 'antiguo':
                $query->oldest();
                break;
            case 'mayor_deuda':
                $query->orderByRaw('(total - total_pagado) DESC');
                break;
            case 'menor_deuda':
                $query->orderByRaw('(total - total_pagado) ASC');
                break;
        }

        $ventas = $query->paginate(15);
        $totalVentas = Venta::ventasAPlazo()->count();
        $totalPendiente = Venta::ventasAPlazo()->sum(DB::raw('total - total_pagado'));

        return view('pagos.index', compact('ventas', 'totalVentas', 'totalPendiente'));
    }

    /**
     * Mostrar detalle de venta con historial de pagos
     */
    public function show(Venta $venta)
    {
        // Verificar que sea venta a plazos
        if (!$venta->es_a_plazos) {
            return redirect()->route('ventas.show', $venta)
                ->with('warning', 'Esta no es una venta a plazos');
        }

        $venta->load(['cliente', 'pagos', 'movimientos.libro']);
        
        return view('pagos.show', compact('venta'));
    }

    /**
     * Registrar un nuevo pago
     */
    public function store(Request $request, Venta $venta)
    {
        // Verificar que la venta no esté cancelada
        if ($venta->estado === 'cancelada') {
            return redirect()->route('ventas.show', $venta)
                ->with('error', 'No se pueden registrar pagos en una venta cancelada');
        }

        $validated = $request->validate([
            'fecha_pago' => 'required|date',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:contado,credito',
            'comprobante' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
        ], [
            'fecha_pago.required' => 'La fecha de pago es obligatoria',
            'monto.required' => 'El monto es obligatorio',
            'monto.min' => 'El monto debe ser mayor a 0',
            'metodo_pago.required' => 'El tipo de pago es obligatorio',
        ]);

        DB::beginTransaction();
        try {

            // Verificar que no exceda el total
            $nuevoTotal = $venta->total_pagado + $validated['monto'];
            if ($nuevoTotal > $venta->total) {
                return back()->withErrors([
                    'error' => 'El monto excede el saldo pendiente. Máximo: $' . number_format($venta->saldo_pendiente, 2)
                ])->withInput();
            }

            // Crear el pago (agregar venta_id)
            $validated['venta_id'] = $venta->id;
            $pago = Pago::create($validated);

            // Actualizar el estado de pago de la venta
            $venta->actualizarEstadoPago();

            // Si la venta se completó, descontar el stock
            if ($venta->estado_pago === 'completado') {
                foreach ($venta->movimientos as $movimiento) {
                    $movimiento->libro->decrement('stock', $movimiento->cantidad);
                }
            }

            DB::commit();

            // Si se completó el pago, redirigir a la venta
            if ($venta->fresh()->estado_pago === 'completado') {
                return redirect()->route('ventas.show', $venta)
                    ->with('success', '¡Pago registrado exitosamente! La venta ha sido completada y el stock descontado.');
            }

            // Si aún hay saldo, volver al formulario de pagos
            return redirect()->route('ventas.pagos.create', $venta)
                ->with('success', 'Pago registrado exitosamente. Saldo pendiente: $' . number_format($venta->fresh()->saldo_pendiente, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el pago: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Eliminar un pago
     */
    public function destroy(Pago $pago)
    {
        // Verificar que la venta no esté cancelada
        if ($pago->venta->estado === 'cancelada') {
            return back()->with('error', 'No se pueden eliminar pagos de una venta cancelada');
        }

        DB::beginTransaction();
        try {
            $venta = $pago->venta;
            $estadoAnterior = $venta->estado_pago;
            
            // Eliminar el pago
            $pago->delete();

            // Actualizar el estado de pago de la venta
            $venta->actualizarEstadoPago();

            // Si antes estaba completado y ahora no, restaurar el stock
            if ($estadoAnterior === 'completado' && $venta->estado_pago !== 'completado') {
                foreach ($venta->movimientos as $movimiento) {
                    $movimiento->libro->increment('stock', $movimiento->cantidad);
                }
            }

            DB::commit();

            return back()->with('success', 'Pago eliminado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar el pago: ' . $e->getMessage()]);
        }
    }
}
