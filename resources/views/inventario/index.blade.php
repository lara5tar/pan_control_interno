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
    <x-card class="overflow-visible">
        <form method="GET" action="{{ route('inventario.index') }}" class="overflow-visible">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[1fr_auto_auto_auto] gap-4 mb-4 overflow-visible items-end">
                <!-- Búsqueda por nombre o código -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search text-gray-400"></i> Búsqueda
                    </label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Buscar por nombre o código..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                </div>

                <!-- Filtro por stock -->
                <div class="w-full md:w-40">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-boxes text-gray-400"></i> Stock
                    </label>
                    <select name="stock_filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos</option>
                        <option value="sin_stock" {{ request('stock_filter') == 'sin_stock' ? 'selected' : '' }}>Sin stock</option>
                        <option value="bajo_stock" {{ request('stock_filter') == 'bajo_stock' ? 'selected' : '' }}>Stock bajo (≤5)</option>
                        <option value="stock_medio" {{ request('stock_filter') == 'stock_medio' ? 'selected' : '' }}>Stock medio (6-20)</option>
                        <option value="stock_alto" {{ request('stock_filter') == 'stock_alto' ? 'selected' : '' }}>Stock alto (>20)</option>
                    </select>
                </div>

                <!-- Filtro por rango de precio -->
                <div class="w-full md:w-40">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-dollar-sign text-gray-400"></i> Precio
                    </label>
                    <select name="precio_filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos</option>
                        <option value="bajo" {{ request('precio_filter') == 'bajo' ? 'selected' : '' }}>Bajo ($0-$50)</option>
                        <option value="medio" {{ request('precio_filter') == 'medio' ? 'selected' : '' }}>Medio ($51-$150)</option>
                        <option value="alto" {{ request('precio_filter') == 'alto' ? 'selected' : '' }}>Alto (>$150)</option>
                    </select>
                </div>

                <!-- Ordenar por -->
                <div class="w-full md:w-40">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort text-gray-400"></i> Ordenar
                    </label>
                    <select name="ordenar" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="reciente" {{ request('ordenar', 'reciente') == 'reciente' ? 'selected' : '' }}>Más recientes</option>
                        <option value="nombre_asc" {{ request('ordenar') == 'nombre_asc' ? 'selected' : '' }}>Nombre (A-Z)</option>
                        <option value="nombre_desc" {{ request('ordenar') == 'nombre_desc' ? 'selected' : '' }}>Nombre (Z-A)</option>
                        <option value="precio_asc" {{ request('ordenar') == 'precio_asc' ? 'selected' : '' }}>Precio (menor)</option>
                        <option value="precio_desc" {{ request('ordenar') == 'precio_desc' ? 'selected' : '' }}>Precio (mayor)</option>
                        <option value="stock_asc" {{ request('ordenar') == 'stock_asc' ? 'selected' : '' }}>Stock (menor)</option>
                        <option value="stock_desc" {{ request('ordenar') == 'stock_desc' ? 'selected' : '' }}>Stock (mayor)</option>
                    </select>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <x-button type="submit" variant="primary" icon="fas fa-filter">
                    Aplicar Filtros
                </x-button>

                @if(request()->hasAny(['search', 'stock_filter', 'precio_filter', 'ordenar']))
                    <x-button type="button" variant="secondary" icon="fas fa-times" 
                              onclick="window.location='{{ route('inventario.index') }}'">
                        Limpiar Filtros
                    </x-button>
                @endif
            </div>
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
