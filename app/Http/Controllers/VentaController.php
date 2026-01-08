<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Libro;
use App\Models\Movimiento;
use App\Services\CodeGeneratorService;
use App\Services\ExcelReportService;
use App\Services\PdfReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    protected $codeGenerator;
    protected $excelReportService;
    protected $pdfReportService;

    public function __construct(
        CodeGeneratorService $codeGenerator,
        ExcelReportService $excelReportService,
        PdfReportService $pdfReportService
    ) {
        $this->codeGenerator = $codeGenerator;
        $this->excelReportService = $excelReportService;
        $this->pdfReportService = $pdfReportService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Venta::with(['movimientos.libro', 'cliente', 'pagos', 'apartado']);

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

        // Filtro por apartados
        if ($request->filled('es_apartado')) {
            if ($request->es_apartado == '1') {
                $query->esApartado(true);
            } elseif ($request->es_apartado == '0') {
                $query->esApartado(false);
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
        $ventasActivas = $ventas->where('estado', '!=', 'cancelada');
        
        return [
            'total_ventas' => $ventasActivas->count(),
            'total_monto' => $ventasActivas->sum('total'),
            'total_pagado' => $ventasActivas->sum('total_pagado'),
            'total_pendiente' => $ventasActivas->sum(function($v) { 
                return $v->total - $v->total_pagado; 
            }),
            'ventas_completadas' => $ventas->where('estado', 'completada')->count(),
            'ventas_canceladas' => $ventas->where('estado', 'cancelada')->count(),
            'ventas_a_plazos' => $ventasActivas->where('es_a_plazos', true)->count(),
            'ventas_vencidas' => $ventasActivas->filter(function($v) {
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
        // Obtener libros con stock disponible en inventario general
        $libros = Libro::where('stock', '>', 0)
            ->orderBy('nombre')
            ->get();

        // Obtener subinventarios activos con sus libros
        $subinventarios = \App\Models\SubInventario::where('estado', 'activo')
            ->with('libros')
            ->orderBy('fecha_subinventario', 'desc')
            ->get()
            ->map(function($sub) {
                $sub->libros_data = $sub->libros->map(function($l) {
                    return [
                        'id' => $l->id,
                        'nombre' => $l->nombre,
                        'codigo_barras' => $l->codigo_barras,
                        'precio' => $l->precio,
                        'stock' => $l->pivot->cantidad  // Usar 'stock' para que sea consistente con el componente
                    ];
                });
                return $sub;
            });

        return view('ventas.create', compact('libros', 'subinventarios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_inventario' => 'required|in:general,subinventario',
            'subinventario_id' => 'nullable|required_if:tipo_inventario,subinventario|exists:subinventarios,id',
            'cliente_id' => 'nullable|exists:clientes,id',
            'fecha_venta' => 'required|date',
            'tipo_pago' => 'required|in:contado,credito,mixto',
            'descuento_global' => 'nullable|numeric|min:0|max:100',
            'observaciones' => 'nullable|string|max:500',
            'es_a_plazos' => 'nullable|boolean',
            'tiene_envio' => 'nullable|boolean',
            'costo_envio' => 'nullable|numeric|min:0',
            'fecha_limite' => 'nullable|date|after:today',
            
            // Movimientos
            'libros' => 'required|array|min:1',
            'libros.*.libro_id' => 'required|exists:libros,id',
            'libros.*.cantidad' => 'required|integer|min:1',
            'libros.*.descuento' => 'nullable|numeric|min:0|max:100',
            'libros.*.precio_custom' => 'nullable|numeric|min:0',
        ], [
            'tipo_inventario.required' => 'Debes seleccionar el tipo de inventario',
            'subinventario_id.required_if' => 'Debes seleccionar un subinventario',
            'fecha_venta.required' => 'La fecha de venta es obligatoria',
            'tipo_pago.required' => 'Debes seleccionar el tipo de pago',
            'libros.required' => 'Debes agregar al menos un libro a la venta',
            'libros.min' => 'Debes agregar al menos un libro a la venta',
            'fecha_limite.after' => 'La fecha límite debe ser posterior a hoy',
        ]);

        DB::beginTransaction();
        try {
            $esAPLazos = isset($validated['es_a_plazos']) && $validated['es_a_plazos'];
            $tipoInventario = $validated['tipo_inventario'];
            $subinventarioId = $validated['subinventario_id'] ?? null;
            
            // VALIDACIÓN: Si es subinventario, verificar que el usuario tenga acceso
            if ($tipoInventario === 'subinventario' && $subinventarioId) {
                $tieneAcceso = DB::table('subinventario_user')
                    ->where('subinventario_id', $subinventarioId)
                    ->where('cod_congregante', session('codCongregante'))
                    ->exists();
                
                if (!$tieneAcceso) {
                    return back()->withErrors([
                        'error' => 'No tienes acceso a este punto de venta (subinventario)'
                    ])->withInput();
                }
            }
            
            // Validar que si es a plazos, debe tener cliente
            if ($esAPLazos && empty($validated['cliente_id'])) {
                return back()->withErrors([
                    'error' => 'Las ventas a plazos requieren un cliente asignado'
                ])->withInput();
            }
            
            // Validar stock según el tipo de inventario
            if ($tipoInventario === 'general') {
                // Validación para inventario general
                if (!$esAPLazos) {
                    foreach ($validated['libros'] as $item) {
                        $libro = Libro::findOrFail($item['libro_id']);
                        
                        if ($libro->stock < $item['cantidad']) {
                            return back()->withErrors([
                                'error' => "Stock en inventario general insuficiente para '{$libro->nombre}'. Stock disponible: {$libro->stock}"
                            ])->withInput();
                        }
                    }
                }
            } else {
                // Validación para subinventario
                $subinventario = \App\Models\SubInventario::with('libros')->findOrFail($subinventarioId);
                
                foreach ($validated['libros'] as $item) {
                    $libroEnSub = $subinventario->libros->firstWhere('id', $item['libro_id']);
                    
                    if (!$libroEnSub) {
                        $libro = Libro::find($item['libro_id']);
                        return back()->withErrors([
                            'error' => "El libro '{$libro->nombre}' no está en este subinventario"
                        ])->withInput();
                    }
                    
                    if ($libroEnSub->pivot->cantidad < $item['cantidad']) {
                        return back()->withErrors([
                            'error' => "Cantidad insuficiente en subinventario para '{$libroEnSub->nombre}'. Disponible: {$libroEnSub->pivot->cantidad}"
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
                'observaciones' => $validated['observaciones'] ?? '',
                'usuario' => session('username'),
                'tipo_inventario' => $tipoInventario,
                'subinventario_id' => $subinventarioId,
                'es_a_plazos' => $esAPLazos,
                'tiene_envio' => isset($validated['tiene_envio']) && $validated['tiene_envio'],
                'costo_envio' => isset($validated['tiene_envio']) && $validated['tiene_envio'] ? ($validated['costo_envio'] ?? 0) : 0,
                'fecha_limite' => $validated['fecha_limite'] ?? null,
                'estado_pago' => $esAPLazos ? 'pendiente' : 'completado',
                'total_pagado' => 0,
            ]);

            // Crear los movimientos asociados
            foreach ($validated['libros'] as $item) {
                $libro = Libro::findOrFail($item['libro_id']);

                // Determinar el precio unitario a usar
                // Si es admin y especificó un precio personalizado, usarlo
                $precioUnitario = $libro->precio;
                
                if ($this->isAdmin() && isset($item['precio_custom']) && !empty($item['precio_custom'])) {
                    $precioUnitario = floatval($item['precio_custom']);
                }

                // Crear movimiento
                $movimiento = Movimiento::create([
                    'venta_id' => $venta->id,
                    'libro_id' => $libro->id,
                    'tipo_movimiento' => 'salida',
                    'tipo_salida' => 'venta',
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $item['descuento'] ?? 0,
                    'fecha' => $validated['fecha_venta'],
                    'observaciones' => "Venta #{$venta->id}" . ($tipoInventario === 'subinventario' ? " - SubInv #{$subinventarioId}" : ''),
                    'usuario' => session('username'),
                ]);

                // Actualizar stock según el tipo de inventario
                if (!$esAPLazos) {
                    // Solo restar del inventario general
                    $libro->decrement('stock', $item['cantidad']);
                    
                    if ($tipoInventario === 'subinventario') {
                        // Actualizar subinventario
                        $subinventario = \App\Models\SubInventario::findOrFail($subinventarioId);
                        $cantidadActual = $subinventario->libros()->where('libro_id', $item['libro_id'])->first()->pivot->cantidad;
                        $nuevaCantidad = $cantidadActual - $item['cantidad'];
                        
                        if ($nuevaCantidad > 0) {
                            $subinventario->libros()->updateExistingPivot($item['libro_id'], [
                                'cantidad' => $nuevaCantidad
                            ]);
                        } else {
                            $subinventario->libros()->detach($item['libro_id']);
                        }
                        
                        // Actualizar contador de stock_subinventario del libro
                        $libro->decrement('stock_subinventario', $item['cantidad']);
                    }
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
        $venta->load(['movimientos.libro', 'cliente', 'apartado', 'subinventario']);
        
        return view('ventas.show', compact('venta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venta $venta)
    {
        // Solo permitir editar a usuarios con rol ADMIN LIBRERIA
        if (!$this->isAdmin()) {
            return redirect()->route('ventas.index')
                ->with('error', 'No tienes permisos para editar ventas. Solo administradores.');
        }

        // Cargar relaciones necesarias
        $venta->load(['movimientos.libro', 'cliente', 'pagos']);

        // Obtener todos los libros disponibles (con stock > 0)
        $libros = Libro::where('stock', '>', 0)
            ->orderBy('nombre')
            ->get();

        // Agregar los libros que están en esta venta pero que podrían no tener stock
        // o incluso haber sido eliminados (para permitir su edición)
        $librosEnVenta = $venta->movimientos->pluck('libro_id')->toArray();
        $librosAdicionales = Libro::whereIn('id', $librosEnVenta)
            ->whereNotIn('id', $libros->pluck('id'))
            ->orderBy('nombre')
            ->get();
        
        // Combinar ambas colecciones
        $libros = $libros->merge($librosAdicionales);

        // No necesitamos subinventarios en edición
        $subinventarios = collect([]);

        return view('ventas.edit', compact('venta', 'libros', 'subinventarios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta)
    {
        // Solo permitir actualizar a usuarios con rol ADMIN LIBRERIA
        if (!$this->isAdmin()) {
            return redirect()->route('ventas.index')
                ->with('error', 'No tienes permisos para editar ventas. Solo administradores.');
        }

        $validated = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'fecha_venta' => 'required|date',
            'tipo_pago' => 'required|in:contado,credito,mixto',
            'observaciones' => 'nullable|string|max:500',
            'descuento_global' => 'nullable|numeric|min:0|max:100',
            'es_a_plazos' => 'nullable|boolean',
            'tiene_envio' => 'nullable|boolean',
            'costo_envio' => 'nullable|numeric|min:0',
            'fecha_limite' => 'nullable|date|after:today',
            
            // Movimientos
            'libros' => 'required|array|min:1',
            'libros.*.libro_id' => 'required|exists:libros,id',
            'libros.*.cantidad' => 'required|integer|min:1',
            'libros.*.descuento' => 'nullable|numeric|min:0|max:100',
            'libros.*.precio_custom' => 'nullable|numeric|min:0',
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

            // Obtener los movimientos actuales de la venta
            $movimientosActuales = $venta->movimientos()->get();
            $librosActuales = $movimientosActuales->keyBy('libro_id');

            // Validar stock para los libros nuevos o con cantidad incrementada
            // Solo validar si NO es a plazos (las ventas a plazos no descuentan stock inmediatamente)
            if (!$esAPLazos && !$venta->es_a_plazos) {
                foreach ($validated['libros'] as $item) {
                    $libro = Libro::findOrFail($item['libro_id']);
                    $cantidadActual = $librosActuales->has($item['libro_id']) 
                        ? $librosActuales[$item['libro_id']]->cantidad 
                        : 0;
                    $diferencia = $item['cantidad'] - $cantidadActual;
                    
                    // Si necesitamos más stock del que teníamos
                    if ($diferencia > 0) {
                        // Validar que hay suficiente stock disponible
                        if ($libro->stock < $diferencia) {
                            return back()->withErrors([
                                'error' => "Stock insuficiente para '{$libro->nombre}'. Stock disponible: {$libro->stock}, se necesitan {$diferencia} unidades adicionales."
                            ])->withInput();
                        }
                    }
                }
            }

            // Restaurar el stock de los movimientos que se van a eliminar o modificar
            // Solo si NO era a plazos (porque las ventas a plazos no descuentan stock)
            if (!$venta->es_a_plazos) {
                foreach ($movimientosActuales as $movimiento) {
                    $libro = $movimiento->libro;
                    // Solo restaurar stock si el libro todavía existe
                    if ($libro) {
                        $libro->increment('stock', $movimiento->cantidad);
                    }
                }
            }

            // Eliminar todos los movimientos actuales
            $venta->movimientos()->delete();

            // Calcular totales
            $subtotal = 0;
            $descuentoGlobal = $validated['descuento_global'] ?? 0;

            // Crear los nuevos movimientos y actualizar stock
            foreach ($validated['libros'] as $item) {
                $libro = Libro::findOrFail($item['libro_id']);
                $cantidad = $item['cantidad'];
                $descuentoItem = $item['descuento'] ?? 0;
                
                // Determinar el precio unitario a usar
                // Si es admin y especificó un precio personalizado, usarlo
                $precioUnitario = $libro->precio;
                
                if ($this->isAdmin() && isset($item['precio_custom']) && !empty($item['precio_custom'])) {
                    $precioUnitario = floatval($item['precio_custom']);
                }
                
                $precioConDescuento = $precioUnitario * (1 - $descuentoItem / 100);
                $subtotalItem = $precioConDescuento * $cantidad;
                
                $subtotal += $subtotalItem;

                // Crear movimiento
                Movimiento::create([
                    'libro_id' => $libro->id,
                    'venta_id' => $venta->id,
                    'tipo_movimiento' => 'salida',
                    'tipo_salida' => 'venta',
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $descuentoItem,
                    'observaciones' => 'Actualización de venta',
                    'usuario' => session('username'),
                    'fecha' => $validated['fecha_venta'],
                ]);

                // Descontar stock solo si NO es a plazos
                if (!$esAPLazos) {
                    $libro->decrement('stock', $cantidad);
                }
            }

            // Calcular total con descuento global y costo de envío
            $descuentoMonto = $subtotal * ($descuentoGlobal / 100);
            $total = $subtotal - $descuentoMonto;
            
            // Sumar costo de envío si aplica
            $costoEnvio = isset($validated['tiene_envio']) && $validated['tiene_envio'] ? ($validated['costo_envio'] ?? 0) : 0;
            if ($costoEnvio > 0) {
                $total += $costoEnvio;
            }

            // Actualizar la venta
            $venta->update([
                'cliente_id' => $validated['cliente_id'],
                'fecha_venta' => $validated['fecha_venta'],
                'tipo_pago' => $validated['tipo_pago'],
                'subtotal' => $subtotal,
                'descuento_global' => $descuentoGlobal,
                'total' => $total,
                'observaciones' => $validated['observaciones'] ?? '',
                'es_a_plazos' => $esAPLazos,
                'tiene_envio' => isset($validated['tiene_envio']) && $validated['tiene_envio'],
                'costo_envio' => $costoEnvio,
                'fecha_limite' => $validated['fecha_limite'] ?? null,
                'estado_pago' => $esAPLazos ? 'pendiente' : 'completado',
            ]);

            DB::commit();

            return redirect()->route('ventas.show', $venta)
                ->with('success', 'Venta actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar la venta: ' . $e->getMessage()])
                ->withInput();
        }
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
            // Restaurar el stock y registrar movimientos de entrada
            foreach ($venta->movimientos as $movimiento) {
                $libro = $movimiento->libro;
                $libro->increment('stock', $movimiento->cantidad);
                
                // Verificar si la venta era de un subinventario
                $esDeSubinventario = false;
                $subinventarioId = null;
                if (preg_match('/SubInv #(\d+)/', $movimiento->observaciones, $matches)) {
                    $esDeSubinventario = true;
                    $subinventarioId = $matches[1];
                    
                    // Restaurar el stock en el subinventario
                    $subinventario = \App\Models\SubInventario::find($subinventarioId);
                    if ($subinventario) {
                        $libroEnSub = $subinventario->libros()->where('libro_id', $libro->id)->first();
                        if ($libroEnSub) {
                            // Ya existe en el subinventario, incrementar cantidad
                            $subinventario->libros()->updateExistingPivot($libro->id, [
                                'cantidad' => $libroEnSub->pivot->cantidad + $movimiento->cantidad
                            ]);
                        } else {
                            // No existe, agregarlo
                            $subinventario->libros()->attach($libro->id, [
                                'cantidad' => $movimiento->cantidad
                            ]);
                        }
                        
                        // Incrementar stock_subinventario del libro
                        $libro->increment('stock_subinventario', $movimiento->cantidad);
                    }
                }
                
                // Registrar movimiento de entrada por cancelación de venta
                $observaciones = 'Cancelación de venta #' . $venta->id;
                if ($esDeSubinventario) {
                    $observaciones .= ' - Devuelto a SubInv #' . $subinventarioId;
                }
                
                \App\Models\Movimiento::create([
                    'libro_id' => $libro->id,
                    'tipo_movimiento' => 'entrada',
                    'tipo_entrada' => 'devolucion',
                    'cantidad' => $movimiento->cantidad,
                    'precio_unitario' => $movimiento->precio_unitario,
                    'observaciones' => $observaciones,
                    'fecha' => now(),
                    'venta_id' => $venta->id,
                    'usuario' => session('username', 'Sistema'),
                ]);
            }

            $venta->update(['estado' => 'cancelada']);

            DB::commit();

            return back()->with('success', 'Venta cancelada exitosamente. Stock restaurado y movimientos registrados.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cancelar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Exportar ventas filtradas a Excel
     */
    public function exportExcel(Request $request)
    {
        // Construir query con filtros
        $query = $this->buildFilteredQuery($request);
        $ventas = $query->get();
        
        // Crear spreadsheet
        $spreadsheet = $this->excelReportService->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $row = $this->excelReportService->setTitle($sheet, 'REPORTE DE VENTAS', 'H', 1);
        $row++; // Espacio
        
        // Filtros aplicados
        $filtros = $this->buildFiltersList($request);
        $row = $this->excelReportService->setFilters($sheet, $filtros, $row);
        
        // Estadísticas
        if ($ventas->count() > 0) {
            $totalMonto = $ventas->sum('total');
            $totalUnidades = $ventas->sum(function($v) { return $v->movimientos->sum('cantidad'); });
            $ventasConEnvio = $ventas->where('tiene_envio', true)->count();
            
            $sheet->setCellValue('A' . $row, 'RESUMEN:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            
            $sheet->setCellValue('A' . $row, 'Total de ventas: ' . $ventas->count());
            $row++;
            $sheet->setCellValue('A' . $row, 'Monto total: $' . number_format($totalMonto, 2));
            $row++;
            $sheet->setCellValue('A' . $row, 'Unidades vendidas: ' . $totalUnidades);
            $row++;
            $sheet->setCellValue('A' . $row, 'Ventas con envío: ' . $ventasConEnvio);
            $row += 2; // Espacio
        }
        
        // Encabezados de tabla
        $headers = ['ID', 'Fecha', 'Cliente', 'Origen', 'Apartado', 'Libros', 'Unidades', 'Tipo Pago', 'Desc.', 'Total', 'Envío', 'Estado'];
        $row = $this->excelReportService->setTableHeaders($sheet, $headers, $row);
        
        // Datos
        $data = [];
        foreach ($ventas as $venta) {
            // Determinar origen
            $origen = 'General';
            if ($venta->tipo_inventario === 'subinventario' && $venta->subinventario) {
                $origen = 'SubInv #' . $venta->subinventario->id;
            }
            
            // Determinar si es apartado
            $apartado = 'No';
            if ($venta->esApartado() && $venta->apartado) {
                $apartado = 'Sí (Apt #' . $venta->apartado->id . ')';
            }
            
            $data[] = [
                $venta->id,
                $venta->fecha_venta->format('d/m/Y H:i'),
                $venta->cliente?->nombre ?: 'Sin cliente',
                $origen,
                $apartado,
                $venta->movimientos->count(),
                $venta->movimientos->sum('cantidad'),
                $venta->getTipoPagoLabel(),
                $venta->descuento_global ? $venta->descuento_global . '%' : '0%',
                '$' . number_format($venta->total, 2),
                $venta->tiene_envio ? 'Sí' : 'No',
                $venta->getEstadoUnificadoLabel(),
            ];
        }
        
        $lastRow = $this->excelReportService->fillData($sheet, $data, $row);
        
        // Auto ajustar columnas
        $this->excelReportService->autoSizeColumns($sheet, ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L']);
        
        // Descargar
        $filename = $this->excelReportService->generateFilename('reporte_ventas');
        $this->excelReportService->download($spreadsheet, $filename);
    }

    /**
     * Exportar ventas filtradas a PDF
     */
    public function exportPdf(Request $request)
    {
        // Construir query con filtros
        $query = $this->buildFilteredQuery($request);
        $ventas = $query->get();
        
        // Preparar filtros
        $filtros = $this->buildFiltersList($request);
        
        // Calcular estadísticas (excluyendo canceladas)
        $ventasActivas = $ventas->where('estado', '!=', 'cancelada');
        
        $estadisticas = [
            'total' => $ventasActivas->count(),
            'monto_total' => $ventasActivas->sum('total'),
            'unidades_vendidas' => $ventasActivas->sum(function($v) { return $v->movimientos->sum('cantidad'); }),
            'ventas_con_envio' => $ventasActivas->where('tiene_envio', true)->count(),
            'completadas' => $ventas->where('estado', 'completada')->count(),
            'canceladas' => $ventas->where('estado', 'cancelada')->count(),
        ];
        
        // Obtener estilos base
        $styles = $this->pdfReportService->getBaseStyles();
        
        // Generar PDF
        $filename = $this->pdfReportService->generateFilename('reporte_ventas');
        
        return $this->pdfReportService->generate(
            'ventas.pdf-report',
            compact('ventas', 'filtros', 'estadisticas', 'styles'),
            $filename,
            ['orientation' => 'landscape'] // Landscape para más columnas
        );
    }

    /**
     * Construir query con filtros (helper privado)
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = Venta::with(['movimientos.libro', 'cliente', 'pagos', 'subinventario', 'apartado']);

        // Filtro por rango de fechas
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_venta', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_venta', '<=', $request->fecha_hasta);
        }

        // Filtro por cliente
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->estado($request->estado);
        }

        // Filtro por tipo de pago
        if ($request->filled('tipo_pago')) {
            $query->tipoPago($request->tipo_pago);
        }

        // Filtro por estado de pago
        if ($request->filled('estado_pago')) {
            $query->estadoPago($request->estado_pago);
        }

        // Filtro por apartados
        if ($request->filled('es_apartado')) {
            if ($request->es_apartado == '1') {
                $query->esApartado(true);
            } elseif ($request->es_apartado == '0') {
                $query->esApartado(false);
            }
        }

        // Filtro por ventas vencidas
        if ($request->filled('vencidas') && $request->vencidas == '1') {
            $query->ventasVencidas();
        }

        // Filtro por libro
        if ($request->filled('libro_id')) {
            $query->conLibro($request->libro_id);
        }

        // Ordenar por fecha más reciente
        $query->orderBy('fecha_venta', 'desc');

        return $query;
    }

    /**
     * Construir lista de filtros aplicados (helper privado)
     */
    private function buildFiltersList(Request $request): array
    {
        $filtros = [];
        
        if ($request->filled('cliente_id')) {
            $cliente = \App\Models\Cliente::find($request->cliente_id);
            if ($cliente) {
                $filtros[] = 'Cliente: ' . $cliente->nombre;
            }
        }

        if ($request->filled('libro_id')) {
            $libro = \App\Models\Libro::find($request->libro_id);
            if ($libro) {
                $filtros[] = 'Libro: ' . $libro->nombre;
            }
        }

        if ($request->filled('estado')) {
            $filtros[] = 'Estado: ' . ucfirst($request->estado);
        }

        if ($request->filled('tipo_pago')) {
            $filtros[] = 'Tipo de pago: ' . ucfirst($request->tipo_pago);
        }

        if ($request->filled('estado_pago')) {
            $filtros[] = 'Estado de pago: ' . ucfirst($request->estado_pago);
        }

        if ($request->filled('vencidas') && $request->vencidas == '1') {
            $filtros[] = 'Ventas: Solo vencidas';
        }

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $filtros[] = 'Período: ' . $request->fecha_desde . ' al ' . $request->fecha_hasta;
        } elseif ($request->filled('fecha_desde')) {
            $filtros[] = 'Desde: ' . $request->fecha_desde;
        } elseif ($request->filled('fecha_hasta')) {
            $filtros[] = 'Hasta: ' . $request->fecha_hasta;
        }

        if (empty($filtros)) {
            $filtros[] = 'Sin filtros aplicados - Mostrando todas las ventas';
        }

        return $filtros;
    }

    /**
     * API - Crear una nueva venta desde la app móvil
     */
    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'subinventario_id' => 'required|exists:subinventarios,id',
            'cliente_id' => 'nullable|exists:clientes,id',
            'fecha_venta' => 'required|date',
            'tipo_pago' => 'required|in:contado,credito,mixto',
            'descuento_global' => 'nullable|numeric|min:0|max:100',
            'observaciones' => 'nullable|string|max:500',
            'usuario' => 'required|string',
            
            // Movimientos
            'libros' => 'required|array|min:1',
            'libros.*.libro_id' => 'required|exists:libros,id',
            'libros.*.cantidad' => 'required|integer|min:1',
            'libros.*.descuento' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Validar que los libros estén en el subinventario
            $subinventario = \App\Models\SubInventario::with('libros')->findOrFail($validated['subinventario_id']);
            
            foreach ($validated['libros'] as $item) {
                $libroEnSub = $subinventario->libros->firstWhere('id', $item['libro_id']);
                
                if (!$libroEnSub) {
                    return response()->json([
                        'success' => false,
                        'message' => "El libro ID {$item['libro_id']} no está en este subinventario"
                    ], 422);
                }
                
                if ($libroEnSub->pivot->cantidad < $item['cantidad']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cantidad insuficiente en subinventario para el libro ID {$item['libro_id']}"
                    ], 422);
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
                'usuario' => $validated['usuario'],
                'es_a_plazos' => false,
                'tiene_envio' => false,
                'costo_envio' => 0,
                'estado_pago' => 'completado',
                'total_pagado' => 0,
            ]);

            // Crear los movimientos y actualizar stock
            foreach ($validated['libros'] as $item) {
                $libro = Libro::findOrFail($item['libro_id']);

                // Crear movimiento
                Movimiento::create([
                    'venta_id' => $venta->id,
                    'libro_id' => $libro->id,
                    'tipo_movimiento' => 'salida',
                    'tipo_salida' => 'venta',
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $libro->precio,
                    'descuento' => $item['descuento'] ?? 0,
                    'fecha' => $validated['fecha_venta'],
                    'observaciones' => "Venta #{$venta->id} - SubInv #{$validated['subinventario_id']}",
                    'usuario' => $validated['usuario'],
                ]);

                // Reducir cantidad en subinventario
                $cantidadActual = $subinventario->libros()->where('libro_id', $item['libro_id'])->first()->pivot->cantidad;
                $nuevaCantidad = $cantidadActual - $item['cantidad'];
                
                if ($nuevaCantidad > 0) {
                    $subinventario->libros()->updateExistingPivot($item['libro_id'], [
                        'cantidad' => $nuevaCantidad
                    ]);
                } else {
                    // Si se vendió todo, eliminar del subinventario
                    $subinventario->libros()->detach($item['libro_id']);
                }

                // Actualizar stock del libro
                $libro->decrement('stock', $item['cantidad']);
                $libro->decrement('stock_subinventario', $item['cantidad']);
            }

            // Calcular totales de la venta
            $venta->actualizarTotales();
            $venta->total_pagado = $venta->total;
            $venta->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta creada exitosamente',
                'data' => [
                    'venta_id' => $venta->id,
                    'total' => $venta->total,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica si el usuario actual es administrador
     */
    private function isAdmin()
    {
        $roles = session('roles', []);
        
        if (empty($roles)) {
            return false;
        }
        
        foreach ($roles as $rol) {
            $rolNombre = strtoupper(trim($rol['ROL'] ?? ''));
            if ($rolNombre === 'ADMIN LIBRERIA' || $rolNombre === 'ADMIN LIBRERÍA') {
                return true;
            }
        }
        
        return false;
    }
}

