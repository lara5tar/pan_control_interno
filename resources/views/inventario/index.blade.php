@extends('layouts.app')

@section('title', 'Inventario de Libros')

@section('page-title', 'Inventario de Libros')
@section('page-description', 'Gestión y control del inventario de libros')

@section('content')
<div class="space-y-6">
    <!-- Encabezado con botón de acción -->
    <x-page-header 
        title="Listado de Libros"
        description="Total: {{ $totalLibros }} libros"
        button-text="Registrar Libro"
        button-icon="fas fa-plus"
        :button-route="route('inventario.create')"
    />

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-stat-card 
            icon="fas fa-book"
            label="Total Libros"
            :value="$totalLibros"
            bg-color="bg-gray-800"
            icon-color="text-white"
        />

        <x-stat-card 
            icon="fas fa-boxes"
            label="Stock Total"
            :value="$stockTotal"
            bg-color="bg-green-100"
            icon-color="text-green-600"
        />

        <x-stat-card 
            icon="fas fa-dollar-sign"
            label="Valor Total"
            :value="'$' . number_format($valorTotal, 2)"
            bg-color="bg-purple-100"
            icon-color="text-purple-600"
        />
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
                        <option value="0-100" {{ request('stock_filter') == '0-100' ? 'selected' : '' }}>Menos de 100</option>
                        <option value="100-200" {{ request('stock_filter') == '100-200' ? 'selected' : '' }}>100 a 200</option>
                        <option value="200-300" {{ request('stock_filter') == '200-300' ? 'selected' : '' }}>200 a 300</option>
                        <option value="300-400" {{ request('stock_filter') == '300-400' ? 'selected' : '' }}>300 a 400</option>
                        <option value="400-up" {{ request('stock_filter') == '400-up' ? 'selected' : '' }}>400 o más</option>
                    </select>
                </div>

                <!-- Filtro por precio -->
                <div class="w-full md:w-40">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-dollar-sign text-gray-400"></i> Precio
                    </label>
                    <select name="precio_filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos</option>
                        <option value="0-100" {{ request('precio_filter') == '0-100' ? 'selected' : '' }}>Menos de $100</option>
                        <option value="100-200" {{ request('precio_filter') == '100-200' ? 'selected' : '' }}>$100 a $200</option>
                        <option value="200-300" {{ request('precio_filter') == '200-300' ? 'selected' : '' }}>$200 a $300</option>
                        <option value="300-400" {{ request('precio_filter') == '300-400' ? 'selected' : '' }}>$300 a $400</option>
                        <option value="400-up" {{ request('precio_filter') == '400-up' ? 'selected' : '' }}>$400 o más</option>
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
                               class="text-gray-800 hover:text-gray-900 transition-colors"
                               title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('inventario.edit', $libro->id) }}" 
                               class="text-gray-800 hover:text-gray-900 transition-colors"
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
