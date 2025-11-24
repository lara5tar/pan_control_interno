<?php

namespace App\Http\Controllers;

use App\Models\Apartado;
use App\Models\Libro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApartadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Apartado::with('libros');

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
                $query->orderBy('fecha_apartado', 'asc');
                break;
            case 'fecha_asc':
                $query->orderBy('fecha_apartado', 'asc');
                break;
            case 'fecha_desc':
                $query->orderBy('fecha_apartado', 'desc');
                break;
            default: // reciente
                $query->orderBy('created_at', 'desc');
                break;
        }

        $apartados = $query->paginate(15)->withQueryString();

        return view('apartados.index', compact('apartados'));
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

        return view('apartados.create', compact('libros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_apartado' => 'required|date',
            'descripcion' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
            
            // Libros
            'libros' => 'required|array|min:1',
            'libros.*.libro_id' => 'required|exists:libros,id',
            'libros.*.cantidad' => 'required|integer|min:1',
        ], [
            'fecha_apartado.required' => 'La fecha de apartado es obligatoria',
            'libros.required' => 'Debes agregar al menos un libro al apartado',
            'libros.min' => 'Debes agregar al menos un libro al apartado',
        ]);

        DB::beginTransaction();
        try {
            // Validar stock de todos los libros primero
            foreach ($validated['libros'] as $item) {
                $libro = Libro::findOrFail($item['libro_id']);
                $stockDisponible = $libro->stock - $libro->stock_apartado;
                
                if ($stockDisponible < $item['cantidad']) {
                    return back()->withErrors([
                        'error' => "Stock disponible insuficiente para '{$libro->nombre}'. Stock disponible: {$stockDisponible}"
                    ])->withInput();
                }
            }

            // Crear el apartado
            $apartado = Apartado::create([
                'fecha_apartado' => $validated['fecha_apartado'],
                'descripcion' => $validated['descripcion'],
                'estado' => 'activo',
                'observaciones' => $validated['observaciones'],
                'usuario' => 'Admin', // Cambiar por auth()->user()->name
            ]);

            // Asociar los libros con sus cantidades
            foreach ($validated['libros'] as $item) {
                $apartado->libros()->attach($item['libro_id'], [
                    'cantidad' => $item['cantidad']
                ]);

                // Incrementar el stock apartado
                $libro = Libro::findOrFail($item['libro_id']);
                $libro->increment('stock_apartado', $item['cantidad']);
            }

            DB::commit();

            return redirect()->route('apartados.show', $apartado)
                ->with('success', 'Apartado creado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear el apartado: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Apartado $apartado)
    {
        $apartado->load('libros');
        
        return view('apartados.show', compact('apartado'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Apartado $apartado)
    {
        // Solo permitir editar apartados activos
        if ($apartado->estado !== 'activo') {
            return redirect()->route('apartados.index')
                ->with('warning', 'Solo se pueden editar apartados activos');
        }

        $libros = Libro::orderBy('nombre')->get();

        return view('apartados.edit', compact('apartado', 'libros'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Apartado $apartado)
    {
        // Solo permitir actualizar apartados activos
        if ($apartado->estado !== 'activo') {
            return redirect()->route('apartados.index')
                ->with('warning', 'Solo se pueden editar apartados activos');
        }

        $validated = $request->validate([
            'fecha_apartado' => 'required|date',
            'descripcion' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
            
            // Libros
            'libros' => 'required|array|min:1',
            'libros.*.libro_id' => 'required|exists:libros,id',
            'libros.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Primero devolver el stock apartado de los libros actuales
            foreach ($apartado->libros as $libro) {
                $libro->decrement('stock_apartado', $libro->pivot->cantidad);
            }

            // Validar stock de todos los nuevos libros
            foreach ($validated['libros'] as $item) {
                $libro = Libro::findOrFail($item['libro_id']);
                $stockDisponible = $libro->stock - $libro->stock_apartado;
                
                if ($stockDisponible < $item['cantidad']) {
                    DB::rollBack();
                    return back()->withErrors([
                        'error' => "Stock disponible insuficiente para '{$libro->nombre}'. Stock disponible: {$stockDisponible}"
                    ])->withInput();
                }
            }

            // Actualizar el apartado
            $apartado->update([
                'fecha_apartado' => $validated['fecha_apartado'],
                'descripcion' => $validated['descripcion'],
                'observaciones' => $validated['observaciones'],
            ]);

            // Desasociar libros anteriores
            $apartado->libros()->detach();

            // Asociar los nuevos libros con sus cantidades
            foreach ($validated['libros'] as $item) {
                $apartado->libros()->attach($item['libro_id'], [
                    'cantidad' => $item['cantidad']
                ]);

                // Incrementar el stock apartado
                $libro = Libro::findOrFail($item['libro_id']);
                $libro->increment('stock_apartado', $item['cantidad']);
            }

            DB::commit();

            return redirect()->route('apartados.show', $apartado)
                ->with('success', 'Apartado actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el apartado: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Apartado $apartado)
    {
        // No permitir eliminar apartados completados
        if ($apartado->estado === 'completado') {
            return back()->with('error', 'No se pueden eliminar apartados completados');
        }

        DB::beginTransaction();
        try {
            // Si está activo, devolver el stock apartado
            if ($apartado->estado === 'activo') {
                foreach ($apartado->libros as $libro) {
                    $libro->decrement('stock_apartado', $libro->pivot->cantidad);
                }
            }

            $apartado->delete();
            DB::commit();

            return redirect()->route('apartados.index')
                ->with('success', 'Apartado eliminado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el apartado: ' . $e->getMessage());
        }
    }

    /**
     * Completar un apartado
     */
    public function completar(Apartado $apartado)
    {
        if ($apartado->estado !== 'activo') {
            return back()->with('warning', 'Este apartado no está activo');
        }

        DB::beginTransaction();
        try {
            // Reducir el stock apartado (ya se vendió)
            foreach ($apartado->libros as $libro) {
                $libro->decrement('stock_apartado', $libro->pivot->cantidad);
            }

            $apartado->update(['estado' => 'completado']);
            DB::commit();

            return back()->with('success', 'Apartado completado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al completar el apartado: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar un apartado (devolver inventario)
     */
    public function cancelar(Apartado $apartado)
    {
        if ($apartado->estado === 'cancelado') {
            return back()->with('warning', 'Este apartado ya está cancelado');
        }

        DB::beginTransaction();
        try {
            // Si estaba activo, devolver el stock apartado
            if ($apartado->estado === 'activo') {
                foreach ($apartado->libros as $libro) {
                    $libro->decrement('stock_apartado', $libro->pivot->cantidad);
                }
            }

            $apartado->update(['estado' => 'cancelado']);
            DB::commit();

            return back()->with('success', 'Apartado cancelado exitosamente. Stock devuelto al inventario.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cancelar el apartado: ' . $e->getMessage());
        }
    }

    /**
     * Devolver parcialmente libros del apartado
     */
    public function devolverParcial(Request $request, Apartado $apartado)
    {
        if ($apartado->estado !== 'activo') {
            return back()->with('warning', 'Este apartado no está activo');
        }

        $validated = $request->validate([
            'libro_id' => 'required|exists:libros,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $libro = $apartado->libros()->where('libro_id', $validated['libro_id'])->first();
            
            if (!$libro) {
                return back()->with('error', 'Este libro no está en el apartado');
            }

            if ($validated['cantidad'] > $libro->pivot->cantidad) {
                return back()->with('error', 'La cantidad a devolver es mayor a la cantidad apartada');
            }

            // Devolver al stock y reducir el apartado
            $libroModel = Libro::findOrFail($validated['libro_id']);
            $libroModel->decrement('stock_apartado', $validated['cantidad']);

            // Si se devuelve todo, eliminar la relación
            if ($validated['cantidad'] == $libro->pivot->cantidad) {
                $apartado->libros()->detach($validated['libro_id']);
            } else {
                // Actualizar la cantidad en la tabla pivote
                $apartado->libros()->updateExistingPivot($validated['libro_id'], [
                    'cantidad' => $libro->pivot->cantidad - $validated['cantidad']
                ]);
            }

            // Si no quedan libros en el apartado, cancelarlo
            if ($apartado->libros()->count() == 0) {
                $apartado->update(['estado' => 'cancelado']);
            }

            DB::commit();

            return back()->with('success', 'Stock devuelto parcialmente exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al devolver el stock: ' . $e->getMessage());
        }
    }
}
