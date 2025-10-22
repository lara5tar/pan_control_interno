<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Libro::query();

        // Búsqueda por nombre o código de barras
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_barras', 'like', "%{$search}%");
            });
        }

        // Filtro por stock
        if ($request->filled('stock_filter')) {
            switch ($request->stock_filter) {
                case '0-100':
                    $query->where('stock', '<', 100);
                    break;
                case '100-200':
                    $query->whereBetween('stock', [100, 200]);
                    break;
                case '200-300':
                    $query->whereBetween('stock', [200, 300]);
                    break;
                case '300-400':
                    $query->whereBetween('stock', [300, 400]);
                    break;
                case '400-up':
                    $query->where('stock', '>=', 400);
                    break;
            }
        }

        // Filtro por precio
        if ($request->filled('precio_filter')) {
            switch ($request->precio_filter) {
                case '0-100':
                    $query->where('precio', '<', 100);
                    break;
                case '100-200':
                    $query->whereBetween('precio', [100, 200]);
                    break;
                case '200-300':
                    $query->whereBetween('precio', [200, 300]);
                    break;
                case '300-400':
                    $query->whereBetween('precio', [300, 400]);
                    break;
                case '400-up':
                    $query->where('precio', '>=', 400);
                    break;
            }
        }

        // Ordenamiento
        $ordenar = $request->get('ordenar', 'reciente');
        switch ($ordenar) {
            case 'nombre_asc':
                $query->orderBy('nombre', 'asc');
                break;
            case 'nombre_desc':
                $query->orderBy('nombre', 'desc');
                break;
            default: // reciente
                $query->orderBy('id', 'desc');
                break;
        }

        // Calcular estadísticas antes de paginar (sobre todos los registros filtrados)
        $totalLibros = (clone $query)->count();
        $stockTotal = (clone $query)->sum('stock');
        $valorTotal = (clone $query)->selectRaw('SUM(stock * precio) as valor')->value('valor');

        $libros = $query->paginate(10);

        return view('inventario.index', compact('libros', 'totalLibros', 'stockTotal', 'valorTotal'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inventario.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_barras' => 'required|string|unique:libros,codigo_barras',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ], [
            'nombre.required' => 'El nombre del libro es obligatorio',
            'codigo_barras.required' => 'El código de barras es obligatorio',
            'codigo_barras.unique' => 'Este código de barras ya existe',
            'precio.required' => 'El precio es obligatorio',
            'precio.numeric' => 'El precio debe ser un número',
            'stock.required' => 'El stock es obligatorio',
            'stock.integer' => 'El stock debe ser un número entero',
        ]);

        Libro::create($validated);

        return redirect()->route('inventario.index')
            ->with('success', 'Libro agregado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $libro = Libro::findOrFail($id);
        return view('inventario.show', compact('libro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $libro = Libro::findOrFail($id);
        return view('inventario.edit', compact('libro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $libro = Libro::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_barras' => 'required|string|unique:libros,codigo_barras,' . $id,
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $libro->update($validated);

        return redirect()->route('inventario.index')
            ->with('success', 'Libro actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $libro = Libro::findOrFail($id);
        $libro->delete();

        return redirect()->route('inventario.index')
            ->with('success', 'Libro eliminado exitosamente');
    }
}
