<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Libro;
use App\Models\Movimiento;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    protected $codeGenerator;

    public function __construct(CodeGeneratorService $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Venta::with(['movimientos.libro', 'cliente', 'pagos']);

        // ===== FILTROS PARA REPORTES =====

        // Filtro por rango de fechas (MUY IMPORTANTE PARA REPORTES)
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_venta', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_venta', '<=', $request->fecha_hasta);
        }

        // Filtro por cliente específico
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        // Filtro por estado de la venta
        if ($request->filled('estado')) {
            $query->estado($request->estado);
        }

        // Filtro por tipo de pago
        if ($request->filled('tipo_pago')) {
            $query->tipoPago($request->tipo_pago);
        }

        // Filtro por estado de pago (para ventas a plazos)
        if ($request->filled('estado_pago')) {
            $query->estadoPago($request->estado_pago);
        }

        // Filtro por ventas a plazos
        if ($request->filled('es_a_plazos')) {
            if ($request->es_a_plazos == '1') {
                $query->ventasAPlazo();
            } elseif ($request->es_a_plazos == '0') {
                $query->where('es_a_plazos', false);
            }
        }

        // Filtro por ventas vencidas (a plazos que pasaron su fecha límite sin pagar)
        if ($request->filled('vencidas') && $request->vencidas == '1') {
            $query->ventasVencidas();
        }

        // Filtro por rango de montos
        if ($request->filled('monto_min')) {
            $query->where('total', '>=', $request->monto_min);
        }
        if ($request->filled('monto_max')) {
            $query->where('total', '<=', $request->monto_max);
        }

        // Filtro por libro específico vendido
        if ($request->filled('libro_id')) {
            $query->conLibro($request->libro_id);
        }

        // Búsqueda general (ID, cliente, observaciones)
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Ordenar
        $ordenar = $request->get('ordenar', 'reciente');
        switch ($ordenar) {
            case 'antiguo':
                $query->orderBy('fecha_venta', 'asc');
                break;
            case 'monto_mayor':
                $query->orderBy('total', 'desc');
                break;
            case 'monto_menor':
                $query->orderBy('total', 'asc');
                break;
            case 'cliente':
                $query->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
                      ->orderBy('clientes.nombre', 'asc')
                      ->select('ventas.*');
                break;
            case 'saldo_mayor':
                $query->orderByRaw('(total - total_pagado) DESC');
                break;
            default: // reciente
                $query->orderBy('fecha_venta', 'desc');
                break;
        }

        $ventas = $query->paginate(15)->withQueryString();

        // Calcular estadísticas para la vista
        $estadisticas = $this->calcularEstadisticas($query);

        // Obtener clientes para el filtro
        $clientes = \App\Models\Cliente::orderBy('nombre')->get();
        
        // Obtener libros para el filtro
        $libros = \App\Models\Libro::orderBy('nombre')->get();

        return view('ventas.index', compact('ventas', 'estadisticas', 'clientes', 'libros'));
    }

    /**
     * Calcular estadísticas de las ventas filtradas
     */
    private function calcularEstadisticas($query)
    {
        // Clonar query para no afectar la paginación
        $queryStats = clone $query;
        
        $ventas = $queryStats->get();
        
        return [
            'total_ventas' => $ventas->count(),
            'total_monto' => $ventas->sum('total'),
            'total_pagado' => $ventas->sum('total_pagado'),
            'total_pendiente' => $ventas->sum(function($v) { 
                return $v->total - $v->total_pagado; 
            }),
            'ventas_completadas' => $ventas->where('estado', 'completada')->count(),
            'ventas_canceladas' => $ventas->where('estado', 'cancelada')->count(),
            'ventas_a_plazos' => $ventas->where('es_a_plazos', true)->count(),
            'ventas_vencidas' => $ventas->filter(function($v) {
                return $v->es_a_plazos && 
                       $v->estado_pago !== 'completado' && 
                       $v->fecha_limite && 
                       $v->fecha_limite->isPast();
            })->count(),
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $libros = Libro::where('stock', '>', 0)
            ->orderBy('nombre')
            ->get();

        return view('ventas.create', compact('libros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'fecha_venta' => 'required|date',
            'tipo_pago' => 'required|in:contado,credito,mixto',
            'descuento_global' => 'nullable|numeric|min:0|max:100',
            'observaciones' => 'nullable|string|max:500',
            'es_a_plazos' => 'nullable|boolean',
            'fecha_limite' => 'nullable|date|after:today',
            
            // Movimientos
            'libros' => 'required|array|min:1',
            'libros.*.libro_id' => 'required|exists:libros,id',
            'libros.*.cantidad' => 'required|integer|min:1',
            'libros.*.descuento' => 'nullable|numeric|min:0|max:100',
        ], [
            'fecha_venta.required' => 'La fecha de venta es obligatoria',
            'tipo_pago.required' => 'Debes seleccionar el tipo de pago',
            'libros.required' => 'Debes agregar al menos un libro a la venta',
            'libros.min' => 'Debes agregar al menos un libro a la venta',
            'fecha_limite.after' => 'La fecha límite debe ser posterior a hoy',
        ]);

        DB::beginTransaction();
        try {
            $esAPLazos = isset($validated['es_a_plazos']) && $validated['es_a_plazos'];
            
            // Validar que si es a plazos, debe tener cliente
            if ($esAPLazos && empty($validated['cliente_id'])) {
                return back()->withErrors([
                    'error' => 'Las ventas a plazos requieren un cliente asignado'
                ])->withInput();
            }
            
            // Validar stock de todos los libros primero (solo si NO es a plazos)
            if (!$esAPLazos) {
                foreach ($validated['libros'] as $item) {
                    $libro = Libro::findOrFail($item['libro_id']);
                    if ($libro->stock < $item['cantidad']) {
                        return back()->withErrors([
                            'error' => "Stock insuficiente para '{$libro->nombre}'. Stock actual: {$libro->stock}"
                        ])->withInput();
                    }
                }
            }

            // Crear la venta
            $venta = Venta::create([
                'cliente_id' => $validated['cliente_id'],
                'fecha_venta' => $validated['fecha_venta'],
                'tipo_pago' => $validated['tipo_pago'],
                'descuento_global' => $validated['descuento_global'] ?? 0,
                'estado' => 'completada',
                'observaciones' => $validated['observaciones'],
                'usuario' => 'Admin', // Cambiar por auth()->user()->name
                'es_a_plazos' => $esAPLazos,
                'fecha_limite' => $validated['fecha_limite'] ?? null,
                'estado_pago' => $esAPLazos ? 'pendiente' : 'completado',
                'total_pagado' => 0,
            ]);

            // Crear los movimientos asociados
            foreach ($validated['libros'] as $item) {
                $libro = Libro::findOrFail($item['libro_id']);

                // Crear movimiento
                $movimiento = Movimiento::create([
                    'venta_id' => $venta->id,
                    'libro_id' => $libro->id,
                    'tipo_movimiento' => 'salida',
                    'tipo_salida' => 'venta',
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $libro->precio,
                    'descuento' => $item['descuento'] ?? 0,
                    'fecha' => $validated['fecha_venta'],
                    'observaciones' => "Venta #{$venta->id}",
                    'usuario' => 'Admin',
                ]);

                // Actualizar stock SOLO si NO es a plazos
                if (!$esAPLazos) {
                    $libro->decrement('stock', $item['cantidad']);
                }
            }

            // Calcular y actualizar totales de la venta
            $venta->actualizarTotales();
            
            // Si NO es a plazos, marcar como completamente pagada
            if (!$esAPLazos) {
                $venta->total_pagado = $venta->total;
                $venta->save();
            }

            DB::commit();

            return redirect()->route('ventas.show', $venta)
                ->with('success', 'Venta registrada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar la venta: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        $venta->load(['movimientos.libro', 'cliente']);
        
        return view('ventas.show', compact('venta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venta $venta)
    {
        // Solo permitir editar ventas pendientes
        if ($venta->estado !== 'pendiente') {
            return redirect()->route('ventas.index')
                ->with('warning', 'Solo se pueden editar ventas pendientes');
        }

        $libros = Libro::where('stock', '>', 0)
            ->orderBy('nombre')
            ->get();

        return view('ventas.edit', compact('venta', 'libros'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta)
    {
        // Solo permitir actualizar ventas pendientes
        if ($venta->estado !== 'pendiente') {
            return redirect()->route('ventas.index')
                ->with('warning', 'Solo se pueden editar ventas pendientes');
        }

        $validated = $request->validate([
            'cliente' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
            'estado' => 'required|in:pendiente,completada,cancelada',
        ]);

        $venta->update($validated);

        return redirect()->route('ventas.show', $venta)
            ->with('success', 'Venta actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venta $venta)
    {
        // No permitir eliminar ventas completadas
        if ($venta->estado === 'completada') {
            return back()->with('error', 'No se pueden eliminar ventas completadas');
        }

        DB::beginTransaction();
        try {
            // Si la venta está cancelada, eliminar movimientos y restaurar stock
            if ($venta->estado === 'cancelada') {
                foreach ($venta->movimientos as $movimiento) {
                    $movimiento->libro->increment('stock', $movimiento->cantidad);
                    $movimiento->delete();
                }
            }

            $venta->delete();
            DB::commit();

            return redirect()->route('ventas.index')
                ->with('success', 'Venta eliminada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar una venta
     */
    public function cancelar(Venta $venta)
    {
        if ($venta->estado === 'cancelada') {
            return back()->with('warning', 'Esta venta ya está cancelada');
        }

        DB::beginTransaction();
        try {
            // Restaurar el stock
            foreach ($venta->movimientos as $movimiento) {
                $movimiento->libro->increment('stock', $movimiento->cantidad);
            }

            $venta->update(['estado' => 'cancelada']);

            DB::commit();

            return back()->with('success', 'Venta cancelada exitosamente. Stock restaurado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cancelar la venta: ' . $e->getMessage());
        }
    }
}
