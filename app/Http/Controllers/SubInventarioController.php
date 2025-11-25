<?php

namespace App\Http\Controllers;

use App\Models\SubInventario;
use App\Models\Libro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubInventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SubInventario::with('libros');

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por fecha
        if ($request->filled('fecha')) {
            $query->porFecha($request->fecha);
        }

        // Búsqueda por descripción
        if ($request->filled('search')) {
            $query->where('descripcion', 'like', '%' . $request->search . '%');
        }

        // Ordenar
        $ordenar = $request->get('ordenar', 'reciente');
        switch ($ordenar) {
            case 'antiguo':
                $query->orderBy('fecha_subinventario', 'asc');
                break;
            case 'fecha_asc':
                $query->orderBy('fecha_subinventario', 'asc');
                break;
            case 'fecha_desc':
                $query->orderBy('fecha_subinventario', 'desc');
                break;
            default: // reciente
                $query->orderBy('created_at', 'desc');
                break;
        }

        $subinventarios = $query->paginate(15)->withQueryString();

        return view('subinventarios.index', compact('subinventarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Solo mostrar libros con stock disponible
        $libros = Libro::where('stock', '>', 0)
            ->orderBy('nombre')
            ->get();

        return view('subinventarios.create', compact('libros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_subinventario' => 'required|date',
            'descripcion' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
            
            // Libros
            'libros' => 'required|array|min:1',
            'libros.*.libro_id' => 'required|exists:libros,id',
            'libros.*.cantidad' => 'required|integer|min:1',
        ], [
            'fecha_subinventario.required' => 'La fecha del sub-inventario es obligatoria',
            'libros.required' => 'Debes agregar al menos un libro al sub-inventario',
            'libros.min' => 'Debes agregar al menos un libro al sub-inventario',
        ]);

        DB::beginTransaction();
        try {
            // Validar stock de todos los libros primero
            foreach ($validated['libros'] as $item) {
                $libro = Libro::findOrFail($item['libro_id']);
                $stockDisponible = $libro->stock - $libro->stock_subinventario; // Cambiaremos este nombre después
                
                if ($stockDisponible < $item['cantidad']) {
                    return back()->withErrors([
                        'error' => "Stock disponible insuficiente para '{$libro->nombre}'. Stock disponible: {$stockDisponible}"
                    ])->withInput();
                }
            }

            // Crear el sub-inventario
            $subinventario = SubInventario::create([
                'fecha_subinventario' => $validated['fecha_subinventario'],
                'descripcion' => $validated['descripcion'],
                'estado' => 'activo',
                'observaciones' => $validated['observaciones'],
                'usuario' => 'Admin', // Cambiar por auth()->user()->name
            ]);

            // Asociar los libros con sus cantidades
            foreach ($validated['libros'] as $item) {
                $subinventario->libros()->attach($item['libro_id'], [
                    'cantidad' => $item['cantidad']
                ]);

                // Incrementar el stock en sub-inventario
                $libro = Libro::findOrFail($item['libro_id']);
                $libro->increment('stock_subinventario', $item['cantidad']); // Cambiaremos este nombre después
            }

            DB::commit();

            return redirect()->route('subinventarios.show', $subinventario)
                ->with('success', 'Sub-inventario creado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear el sub-inventario: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SubInventario $subinventario)
    {
        $subinventario->load('libros');
        
        return view('subinventarios.show', compact('subinventario'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubInventario $subinventario)
    {
        // Solo permitir editar sub-inventarios activos
        if ($subinventario->estado !== 'activo') {
            return redirect()->route('subinventarios.index')
                ->with('warning', 'Solo se pueden editar sub-inventarios activos');
        }

        $libros = Libro::orderBy('nombre')->get();

        return view('subinventarios.edit', compact('subinventario', 'libros'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubInventario $subinventario)
    {
        // Solo permitir actualizar sub-inventarios activos
        if ($subinventario->estado !== 'activo') {
            return redirect()->route('subinventarios.index')
                ->with('warning', 'Solo se pueden editar sub-inventarios activos');
        }

        $validated = $request->validate([
            'fecha_subinventario' => 'required|date',
            'descripcion' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
            
            // Libros
            'libros' => 'required|array|min:1',
            'libros.*.libro_id' => 'required|exists:libros,id',
            'libros.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Primero devolver el stock en sub-inventario de los libros actuales
            foreach ($subinventario->libros as $libro) {
                $libro->decrement('stock_subinventario', $libro->pivot->cantidad); // Cambiaremos este nombre después
            }

            // Validar stock de todos los nuevos libros
            foreach ($validated['libros'] as $item) {
                $libro = Libro::findOrFail($item['libro_id']);
                $stockDisponible = $libro->stock - $libro->stock_subinventario; // Cambiaremos este nombre después
                
                if ($stockDisponible < $item['cantidad']) {
                    DB::rollBack();
                    return back()->withErrors([
                        'error' => "Stock disponible insuficiente para '{$libro->nombre}'. Stock disponible: {$stockDisponible}"
                    ])->withInput();
                }
            }

            // Actualizar el sub-inventario
            $subinventario->update([
                'fecha_subinventario' => $validated['fecha_subinventario'],
                'descripcion' => $validated['descripcion'],
                'observaciones' => $validated['observaciones'],
            ]);

            // Desasociar libros anteriores
            $subinventario->libros()->detach();

            // Asociar los nuevos libros con sus cantidades
            foreach ($validated['libros'] as $item) {
                $subinventario->libros()->attach($item['libro_id'], [
                    'cantidad' => $item['cantidad']
                ]);

                // Incrementar el stock en sub-inventario
                $libro = Libro::findOrFail($item['libro_id']);
                $libro->increment('stock_subinventario', $item['cantidad']); // Cambiaremos este nombre después
            }

            DB::commit();

            return redirect()->route('subinventarios.show', $subinventario)
                ->with('success', 'Sub-inventario actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el sub-inventario: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubInventario $subinventario)
    {
        // No permitir eliminar sub-inventarios completados
        if ($subinventario->estado === 'completado') {
            return back()->with('error', 'No se pueden eliminar sub-inventarios completados');
        }

        DB::beginTransaction();
        try {
            // Si está activo, devolver el stock en sub-inventario
            if ($subinventario->estado === 'activo') {
                foreach ($subinventario->libros as $libro) {
                    $libro->decrement('stock_subinventario', $libro->pivot->cantidad); // Cambiaremos este nombre después
                }
            }

            $subinventario->delete();
            DB::commit();

            return redirect()->route('subinventarios.index')
                ->with('success', 'Sub-inventario eliminado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el sub-inventario: ' . $e->getMessage());
        }
    }

    /**
     * Completar un sub-inventario
     */
    public function completar(SubInventario $subinventario)
    {
        if ($subinventario->estado !== 'activo') {
            return back()->with('warning', 'Este sub-inventario no está activo');
        }

        DB::beginTransaction();
        try {
            // Reducir el stock en sub-inventario (ya se vendió)
            foreach ($subinventario->libros as $libro) {
                $libro->decrement('stock_subinventario', $libro->pivot->cantidad); // Cambiaremos este nombre después
            }

            $subinventario->update(['estado' => 'completado']);
            DB::commit();

            return back()->with('success', 'Sub-inventario completado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al completar el sub-inventario: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar un sub-inventario (devolver inventario)
     */
    public function cancelar(SubInventario $subinventario)
    {
        if ($subinventario->estado === 'cancelado') {
            return back()->with('warning', 'Este sub-inventario ya está cancelado');
        }

        DB::beginTransaction();
        try {
            // Si estaba activo, devolver el stock del sub-inventario
            if ($subinventario->estado === 'activo') {
                foreach ($subinventario->libros as $libro) {
                    $libro->decrement('stock_subinventario', $libro->pivot->cantidad); // Cambiaremos este nombre después
                }
            }

            $subinventario->update(['estado' => 'cancelado']);
            DB::commit();

            return back()->with('success', 'Sub-inventario cancelado exitosamente. Stock devuelto al inventario.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cancelar el sub-inventario: ' . $e->getMessage());
        }
    }

    /**
     * Devolver parcialmente libros del sub-inventario
     */
    public function devolverParcial(Request $request, SubInventario $subinventario)
    {
        if ($subinventario->estado !== 'activo') {
            return back()->with('warning', 'Este sub-inventario no está activo');
        }

        $validated = $request->validate([
            'libro_id' => 'required|exists:libros,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $libro = $subinventario->libros()->where('libro_id', $validated['libro_id'])->first();
            
            if (!$libro) {
                return back()->with('error', 'Este libro no está en el sub-inventario');
            }

            if ($validated['cantidad'] > $libro->pivot->cantidad) {
                return back()->with('error', 'La cantidad a devolver es mayor a la cantidad en sub-inventario');
            }

            // Devolver al stock y reducir el sub-inventario
            $libroModel = Libro::findOrFail($validated['libro_id']);
            $libroModel->decrement('stock_subinventario', $validated['cantidad']); // Cambiaremos este nombre después

            // Si se devuelve todo, eliminar la relación
            if ($validated['cantidad'] == $libro->pivot->cantidad) {
                $subinventario->libros()->detach($validated['libro_id']);
            } else {
                // Actualizar la cantidad en la tabla pivote
                $subinventario->libros()->updateExistingPivot($validated['libro_id'], [
                    'cantidad' => $libro->pivot->cantidad - $validated['cantidad']
                ]);
            }

            // Si no quedan libros en el sub-inventario, cancelarlo
            if ($subinventario->libros()->count() == 0) {
                $subinventario->update(['estado' => 'cancelado']);
            }

            DB::commit();

            return back()->with('success', 'Stock devuelto parcialmente exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al devolver el stock: ' . $e->getMessage());
        }
    }
}
