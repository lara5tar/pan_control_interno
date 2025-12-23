<?php

namespace App\Http\Controllers;

use App\Models\Apartado;
use App\Models\ApartadoDetalle;
use App\Models\Cliente;
use App\Models\Libro;
use App\Models\Venta;
use App\Models\Movimiento;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApartadoController extends Controller
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
        $query = Apartado::with(['cliente', 'detalles.libro', 'abonos']);

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por cliente
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        // Filtro por fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_apartado', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_apartado', '<=', $request->fecha_hasta);
        }

        // Filtro por vencidos
        if ($request->filled('vencidos') && $request->vencidos == '1') {
            $query->vencidos();
        }

        // Búsqueda general
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('folio', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        $ordenar = $request->get('ordenar', 'reciente');
        switch ($ordenar) {
            case 'reciente':
                $query->latest();
                break;
            case 'antiguo':
                $query->oldest();
                break;
            case 'mayor_saldo':
                $query->orderBy('saldo_pendiente', 'desc');
                break;
            case 'menor_saldo':
                $query->orderBy('saldo_pendiente', 'asc');
                break;
        }

        $apartados = $query->paginate(15)->withQueryString();

        // Estadísticas
        $estadisticas = [
            'total_apartados' => Apartado::count(),
            'activos' => Apartado::activos()->count(),
            'liquidados' => Apartado::liquidados()->count(),
            'cancelados' => Apartado::cancelados()->count(),
            'vencidos' => Apartado::vencidos()->count(),
            'total_apartado' => Apartado::activos()->sum('monto_total'),
            'saldo_pendiente_total' => Apartado::activos()->sum('saldo_pendiente'),
        ];

        $clientes = Cliente::orderBy('nombre')->get();

        return view('apartados.index', compact('apartados', 'estadisticas', 'clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $libros = Libro::where('stock', '>', 0)->orderBy('nombre')->get();

        return view('apartados.create', compact('clientes', 'libros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fecha_apartado' => 'required|date',
            'enganche' => 'required|numeric|min:0',
            'fecha_limite' => 'nullable|date|after:today',
            'observaciones' => 'nullable|string',
            'libros' => 'required|array|min:1',
            'libros.*.libro_id' => 'required|exists:libros,id',
            'libros.*.cantidad' => 'required|integer|min:1',
            'libros.*.precio_unitario' => 'required|numeric|min:0',
            'libros.*.descuento' => 'nullable|numeric|min:0|max:100',
        ], [
            'cliente_id.required' => 'Debe seleccionar un cliente',
            'fecha_apartado.required' => 'La fecha de apartado es obligatoria',
            'enganche.required' => 'El enganche es obligatorio',
            'enganche.min' => 'El enganche debe ser mayor o igual a 0',
            'libros.required' => 'Debe agregar al menos un libro',
            'libros.min' => 'Debe agregar al menos un libro',
            'fecha_limite.after' => 'La fecha límite debe ser posterior a hoy',
        ]);

        DB::beginTransaction();
        try {
            // Generar folio único
            $folio = $this->codeGenerator->generateCode('Apartado', 'AP');

            // Calcular monto total
            $montoTotal = 0;
            foreach ($validated['libros'] as $libroData) {
                $libro = Libro::find($libroData['libro_id']);
                
                // Verificar stock disponible
                $stockDisponible = $libro->stock - $libro->stock_apartado;
                if ($stockDisponible < $libroData['cantidad']) {
                    throw new \Exception("Stock insuficiente para el libro: {$libro->nombre}. Disponible: {$stockDisponible}");
                }

                $precio = $libroData['precio_unitario'];
                if (!empty($libroData['descuento'])) {
                    $precio -= ($precio * $libroData['descuento'] / 100);
                }
                $subtotal = $precio * $libroData['cantidad'];
                $montoTotal += $subtotal;
            }

            // Validar que el enganche no sea mayor al total
            if ($validated['enganche'] > $montoTotal) {
                throw new \Exception('El enganche no puede ser mayor al monto total del apartado');
            }

            // Crear apartado
            $apartado = Apartado::create([
                'folio' => $folio,
                'cliente_id' => $validated['cliente_id'],
                'fecha_apartado' => $validated['fecha_apartado'],
                'monto_total' => $montoTotal,
                'enganche' => $validated['enganche'],
                'saldo_pendiente' => $montoTotal - $validated['enganche'],
                'fecha_limite' => $validated['fecha_limite'] ?? null,
                'estado' => 'activo',
                'observaciones' => $validated['observaciones'] ?? null,
                'usuario' => Auth::user()->name ?? 'Sistema',
            ]);

            // Crear detalles y actualizar stock_apartado
            foreach ($validated['libros'] as $libroData) {
                $libro = Libro::find($libroData['libro_id']);
                
                $precio = $libroData['precio_unitario'];
                $descuento = $libroData['descuento'] ?? 0;
                if ($descuento > 0) {
                    $precio -= ($precio * $descuento / 100);
                }
                $subtotal = $precio * $libroData['cantidad'];

                ApartadoDetalle::create([
                    'apartado_id' => $apartado->id,
                    'libro_id' => $libroData['libro_id'],
                    'cantidad' => $libroData['cantidad'],
                    'precio_unitario' => $libroData['precio_unitario'],
                    'descuento' => $descuento,
                    'subtotal' => $subtotal,
                ]);

                // Incrementar stock_apartado
                $libro->increment('stock_apartado', $libroData['cantidad']);
            }

            // Si hubo enganche, crear el primer abono
            if ($validated['enganche'] > 0) {
                $apartado->abonos()->create([
                    'fecha_abono' => $validated['fecha_apartado'],
                    'monto' => $validated['enganche'],
                    'saldo_anterior' => $montoTotal,
                    'saldo_nuevo' => $montoTotal - $validated['enganche'],
                    'metodo_pago' => 'efectivo',
                    'observaciones' => 'Enganche inicial',
                    'usuario' => Auth::user()->name ?? 'Sistema',
                ]);
            }

            DB::commit();

            return redirect()->route('apartados.show', $apartado)
                ->with('success', 'Apartado creado exitosamente. Folio: ' . $folio);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log detallado del error
            Log::error('Error al crear apartado', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => $request->except(['_token']),
                'user' => Auth::user()->name ?? 'Sistema',
            ]);
            
            return back()->withErrors(['error' => 'Error al crear el apartado: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Apartado $apartado)
    {
        $apartado->load(['cliente', 'detalles.libro', 'abonos', 'venta']);
        
        return view('apartados.show', compact('apartado'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Apartado $apartado)
    {
        // Solo permitir editar apartados activos
        if ($apartado->estado !== 'activo') {
            return redirect()->route('apartados.show', $apartado)
                ->with('warning', 'Solo se pueden editar apartados activos');
        }

        // Si ya tiene abonos, no permitir editar los libros
        $tieneAbonos = $apartado->abonos()->count() > 0;

        $apartado->load(['cliente', 'detalles.libro']);
        $clientes = Cliente::orderBy('nombre')->get();
        $libros = Libro::where('stock', '>', 0)->orderBy('nombre')->get();

        return view('apartados.edit', compact('apartado', 'clientes', 'libros', 'tieneAbonos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Apartado $apartado)
    {
        // Solo permitir actualizar apartados activos
        if ($apartado->estado !== 'activo') {
            return redirect()->route('apartados.show', $apartado)
                ->with('error', 'Solo se pueden editar apartados activos');
        }

        $validated = $request->validate([
            'fecha_limite' => 'nullable|date|after:today',
            'observaciones' => 'nullable|string',
        ], [
            'fecha_limite.after' => 'La fecha límite debe ser posterior a hoy',
        ]);

        try {
            $apartado->update([
                'fecha_limite' => $validated['fecha_limite'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            return redirect()->route('apartados.show', $apartado)
                ->with('success', 'Apartado actualizado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error al actualizar apartado', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'apartado_id' => $apartado->id,
                'data' => $request->except(['_token']),
                'user' => Auth::user()->name ?? 'Sistema',
            ]);
            
            return back()->withErrors(['error' => 'Error al actualizar el apartado: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Liquidar un apartado y crear la venta
     */
    public function liquidar(Apartado $apartado)
    {
        if ($apartado->estado !== 'activo') {
            return back()->with('error', 'Solo se pueden liquidar apartados activos');
        }

        if ($apartado->saldo_pendiente > 0) {
            return back()->with('error', 'El apartado aún tiene saldo pendiente. Debe estar completamente pagado para liquidar.');
        }

        DB::beginTransaction();
        try {
            // Crear la venta
            $venta = Venta::create([
                'cliente_id' => $apartado->cliente_id,
                'apartado_id' => $apartado->id,
                'fecha_venta' => now(),
                'tipo_pago' => 'contado', // Ya está pagado por los abonos
                'subtotal' => $apartado->monto_total,
                'descuento_global' => 0,
                'total' => $apartado->monto_total,
                'estado' => 'completada',
                'tiene_envio' => false,
                'costo_envio' => 0,
                'observaciones' => "Venta generada del apartado {$apartado->folio}",
                'usuario' => Auth::user()->name ?? 'Sistema',
                'es_a_plazos' => false,
                'total_pagado' => $apartado->monto_total,
                'estado_pago' => 'completado',
            ]);

            // Crear movimientos de salida y descontar inventario
            foreach ($apartado->detalles as $detalle) {
                Movimiento::create([
                    'libro_id' => $detalle->libro_id,
                    'venta_id' => $venta->id,
                    'tipo_movimiento' => 'salida',
                    'tipo_salida' => 'venta',
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'descuento' => $detalle->descuento,
                    'fecha' => now(),
                    'observaciones' => "Venta del apartado {$apartado->folio}",
                    'usuario' => Auth::user()->name ?? 'Sistema',
                ]);

                // Descontar del stock y del stock_apartado
                $libro = $detalle->libro;
                $libro->decrement('stock', $detalle->cantidad);
                $libro->decrement('stock_apartado', $detalle->cantidad);
            }

            // Actualizar apartado
            $apartado->update([
                'estado' => 'liquidado',
                'venta_id' => $venta->id,
            ]);

            DB::commit();

            return redirect()->route('ventas.show', $venta)
                ->with('success', '¡Apartado liquidado exitosamente! Se ha creado la venta #' . $venta->id);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al liquidar apartado', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'apartado_id' => $apartado->id,
                'apartado_folio' => $apartado->folio,
                'apartado_monto_total' => $apartado->monto_total,
                'apartado_saldo_pendiente' => $apartado->saldo_pendiente,
                'user' => Auth::user()->name ?? 'Sistema',
            ]);
            
            return back()->withErrors(['error' => 'Error al liquidar el apartado: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancelar un apartado
     */
    public function cancelar(Apartado $apartado)
    {
        if ($apartado->estado !== 'activo') {
            return back()->with('error', 'Solo se pueden cancelar apartados activos');
        }

        DB::beginTransaction();
        try {
            // Liberar stock_apartado
            foreach ($apartado->detalles as $detalle) {
                $detalle->libro->decrement('stock_apartado', $detalle->cantidad);
            }

            // Cambiar estado
            $apartado->update([
                'estado' => 'cancelado',
            ]);

            DB::commit();

            return redirect()->route('apartados.show', $apartado)
                ->with('success', 'Apartado cancelado exitosamente. El stock ha sido liberado.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al cancelar apartado', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'apartado_id' => $apartado->id,
                'apartado_folio' => $apartado->folio,
                'user' => Auth::user()->name ?? 'Sistema',
            ]);
            
            return back()->withErrors(['error' => 'Error al cancelar el apartado: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Apartado $apartado)
    {
        // Solo permitir eliminar apartados cancelados
        if ($apartado->estado !== 'cancelado') {
            return back()->with('error', 'Solo se pueden eliminar apartados cancelados');
        }

        DB::beginTransaction();
        try {
            $apartado->delete();

            DB::commit();

            return redirect()->route('apartados.index')
                ->with('success', 'Apartado eliminado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al eliminar apartado', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'apartado_id' => $apartado->id,
                'apartado_folio' => $apartado->folio,
                'user' => Auth::user()->name ?? 'Sistema',
            ]);
            
            return back()->withErrors(['error' => 'Error al eliminar el apartado: ' . $e->getMessage()]);
        }
    }
}
