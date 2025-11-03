<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Buscar clientes por nombre
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $clientes = Cliente::where('nombre', 'like', "%{$search}%")
            ->orWhere('telefono', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'nombre', 'telefono']);

        return response()->json($clientes);
    }

    /**
     * Mostrar todos los clientes
     */
    public function index(Request $request)
    {
        $query = Cliente::withCount('ventas');

        // Búsqueda
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        switch ($request->get('ordenar', 'reciente')) {
            case 'nombre_asc':
                $query->orderBy('nombre', 'asc');
                break;
            case 'nombre_desc':
                $query->orderBy('nombre', 'desc');
                break;
            case 'mas_ventas':
                $query->orderBy('ventas_count', 'desc');
                break;
            default:
                $query->latest();
        }

        $clientes = $query->paginate(15);
        
        $totalClientes = Cliente::count();

        return view('clientes.index', compact('clientes', 'totalClientes'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Crear un nuevo cliente
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
        ], [
            'nombre.required' => 'El nombre del cliente es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres',
        ]);

        // Si es una petición AJAX (desde el formulario de ventas)
        if ($request->ajax()) {
            $cliente = Cliente::create($validated);
            return response()->json([
                'success' => true,
                'cliente' => $cliente
            ]);
        }

        // Si es una petición normal
        Cliente::create($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente registrado exitosamente');
    }

    /**
     * Mostrar detalles de un cliente
     */
    public function show(Cliente $cliente)
    {
        $cliente->load(['ventas' => function($query) {
            $query->latest();
        }]);

        return view('clientes.show', compact('cliente'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Actualizar un cliente
     */
    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
        ], [
            'nombre.required' => 'El nombre del cliente es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres',
        ]);

        $cliente->update($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente');
    }

    /**
     * Eliminar un cliente
     */
    public function destroy(Cliente $cliente)
    {
        // Verificar si el cliente tiene ventas
        if ($cliente->ventas()->count() > 0) {
            return redirect()->route('clientes.index')
                ->with('error', 'No se puede eliminar el cliente porque tiene ventas asociadas');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado exitosamente');
    }
}
