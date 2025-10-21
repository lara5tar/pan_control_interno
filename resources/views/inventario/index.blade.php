@extends('layouts.app')

@section('title', 'Inventario de Libros')

@section('page-title', 'Inventario de Libros')
@section('page-description', 'Gestión y control del inventario de libros')

@section('content')
<div class="space-y-6">
    <!-- Encabezado con botón de acción -->
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">Listado de Libros</h3>
            <p class="text-gray-600 text-sm mt-1">Total de libros: {{ $libros->total() }}</p>
        </div>
        <x-button variant="primary" icon="fas fa-plus" onclick="window.location='{{ route('inventario.create') }}'">
            Agregar Libro
        </x-button>
    </div>

    <!-- Filtros y búsqueda -->
    <x-card>
        <form method="GET" action="{{ route('inventario.index') }}" class="flex gap-4">
            <div class="flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Buscar por nombre o código de barras..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
            </div>
            <x-button type="submit" variant="primary" icon="fas fa-search">
                Buscar
            </x-button>
            @if(request('search'))
                <x-button type="button" variant="secondary" icon="fas fa-times" onclick="window.location='{{ route('inventario.index') }}'">
                    Limpiar
                </x-button>
            @endif
        </form>
    </x-card>

    <!-- Tabla de libros -->
    <x-card>
        <x-table 
            :headers="['ID', 'Nombre', 'Código de Barras', 'Precio', 'Stock', 'Acciones']"
            :items="$libros"
            emptyMessage="No hay libros registrados"
            emptyIcon="fas fa-book"
        >
            @foreach($libros as $libro)
                <x-table-row>
                    <x-table-cell>{{ $libro->id }}</x-table-cell>
                    <x-table-cell class="font-medium">{{ $libro->nombre }}</x-table-cell>
                    <x-table-cell class="text-gray-600">{{ $libro->codigo_barras }}</x-table-cell>
                    <x-table-cell>${{ number_format($libro->precio, 2) }}</x-table-cell>
                    <x-table-cell>
                        <x-badge :type="$libro->stock > 10 ? 'success' : ($libro->stock > 0 ? 'warning' : 'danger')">
                            {{ $libro->stock }}
                        </x-badge>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('inventario.show', $libro->id) }}" 
                               class="text-primary-500 hover:text-primary-700 transition-colors"
                               title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('inventario.edit', $libro->id) }}" 
                               class="text-primary-500 hover:text-primary-700 transition-colors"
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('inventario.destroy', $libro->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-900 transition-colors" 
                                        onclick="return confirm('¿Estás seguro de eliminar este libro?')"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </x-table-cell>
                </x-table-row>
            @endforeach
        </x-table>

        <!-- Paginación -->
        @if($libros->hasPages())
            <div class="mt-4 px-6 py-4 border-t border-gray-200">
                {{ $libros->appends(request()->query())->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
