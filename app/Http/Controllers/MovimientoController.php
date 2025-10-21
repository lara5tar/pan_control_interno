<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Libro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Movimiento::with('libro')
            ->orderBy('created_at', 'desc');

        // Filtrar por libro
        if ($request->filled('libro_id')) {
            $query->where('libro_id', $request->libro_id);
        }

        // Filtrar por tipo de movimiento (unificado)
        if ($request->filled('tipo_movimiento')) {
            $tipoMovimiento = $request->tipo_movimiento;
            
            // Verificar si es un tipo específico (entrada_compra, salida_venta, etc.)
            if (str_starts_with($tipoMovimiento, 'entrada_')) {
                $tipo = str_replace('entrada_', '', $tipoMovimiento);
                $query->where('tipo_movimiento', 'entrada')
                      ->where('tipo_entrada', $tipo);
            } elseif (str_starts_with($tipoMovimiento, 'salida_')) {
                $tipo = str_replace('salida_', '', $tipoMovimiento);
                $query->where('tipo_movimiento', 'salida')
                      ->where('tipo_salida', $tipo);
            } else {
                // Es un filtro general (solo "entrada" o "salida")
                $query->where('tipo_movimiento', $tipoMovimiento);
            }
        }

        // Filtrar por fecha
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Calcular estadísticas antes de paginar (sobre todos los registros filtrados)
        $totalEntradas = (clone $query)->where('tipo_movimiento', 'entrada')->sum('cantidad');
        $totalSalidas = (clone $query)->where('tipo_movimiento', 'salida')->sum('cantidad');
        $totalMovimientos = (clone $query)->count();

        $movimientos = $query->paginate(10);
        $libros = Libro::orderBy('nombre')->get();

        return view('movimientos.index', compact('movimientos', 'libros', 'totalEntradas', 'totalSalidas', 'totalMovimientos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $libros = Libro::orderBy('nombre')->get();
        return view('movimientos.create', compact('libros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'libro_id' => 'required|exists:libros,id',
            'tipo_movimiento' => 'required|in:entrada,salida',
            'tipo_entrada' => 'required_if:tipo_movimiento,entrada',
            'tipo_salida' => 'required_if:tipo_movimiento,salida',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ], [
            'libro_id.required' => 'Debes seleccionar un libro',
            'libro_id.exists' => 'El libro seleccionado no existe',
            'tipo_movimiento.required' => 'Debes seleccionar el tipo de movimiento',
            'tipo_entrada.required_if' => 'Debes seleccionar el tipo de entrada',
            'tipo_salida.required_if' => 'Debes seleccionar el tipo de salida',
            'cantidad.required' => 'La cantidad es obligatoria',
            'cantidad.min' => 'La cantidad debe ser al menos 1',
        ]);

        DB::beginTransaction();
        try {
            $libro = Libro::findOrFail($request->libro_id);

            // Validar que hay stock suficiente para salidas
            if ($request->tipo_movimiento === 'salida' && $libro->stock < $request->cantidad) {
                return back()->withErrors(['cantidad' => 'No hay suficiente stock. Stock actual: ' . $libro->stock])
                    ->withInput();
            }

            // Crear el movimiento
            $movimiento = Movimiento::create([
                'libro_id' => $request->libro_id,
                'tipo_movimiento' => $request->tipo_movimiento,
                'tipo_entrada' => $request->tipo_movimiento === 'entrada' ? $request->tipo_entrada : null,
                'tipo_salida' => $request->tipo_movimiento === 'salida' ? $request->tipo_salida : null,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $request->precio_unitario,
                'observaciones' => $request->observaciones,
                'usuario' => 'Admin' // Aquí puedes usar auth()->user()->name cuando tengas autenticación
            ]);

            // Actualizar el stock del libro
            if ($request->tipo_movimiento === 'entrada') {
                $libro->increment('stock', $request->cantidad);
            } else {
                $libro->decrement('stock', $request->cantidad);
            }

            DB::commit();

            return redirect()->route('movimientos.index')
                ->with('success', 'Movimiento registrado exitosamente. Stock actualizado: ' . $libro->fresh()->stock);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el movimiento: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Movimiento $movimiento)
    {
        $movimiento->load('libro');
        return view('movimientos.show', compact('movimiento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Movimiento $movimiento)
    {
        // No permitir editar movimientos para mantener integridad del inventario
        return redirect()->route('movimientos.index')
            ->with('warning', 'Los movimientos no pueden ser editados para mantener la integridad del inventario.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Movimiento $movimiento)
    {
        // No permitir actualizar
        return redirect()->route('movimientos.index')
            ->with('warning', 'Los movimientos no pueden ser editados.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movimiento $movimiento)
    {
        // No permitir eliminar para mantener historial
        return redirect()->route('movimientos.index')
            ->with('warning', 'Los movimientos no pueden ser eliminados para mantener el historial del inventario.');
    }
}
