@extends('layouts.app')

@section('title', 'Apartados de Inventario')

@section('content')
<x-page-layout 
    title="Apartados de Inventario"
    description="Gestiona los apartados de inventario para días de venta"
    button-text="Nuevo Apartado"
    button-icon="fas fa-plus"
    :button-route="route('apartados.create')"
>
    <!-- Filtros -->
    <x-card class="mb-6">
        <form method="GET" action="{{ route('apartados.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Buscar por descripción -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Descripción..."
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Filtro por estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Todos los estados</option>
                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completado</option>
                        <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <!-- Filtro por fecha -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                    <input type="date" 
                           name="fecha" 
                           value="{{ request('fecha') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Ordenar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordenar por</label>
                    <select name="ordenar" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="reciente" {{ request('ordenar') == 'reciente' ? 'selected' : '' }}>Más reciente</option>
                        <option value="antiguo" {{ request('ordenar') == 'antiguo' ? 'selected' : '' }}>Más antiguo</option>
                        <option value="fecha_asc" {{ request('ordenar') == 'fecha_asc' ? 'selected' : '' }}>Fecha apartado (asc)</option>
                        <option value="fecha_desc" {{ request('ordenar') == 'fecha_desc' ? 'selected' : '' }}>Fecha apartado (desc)</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>
                <a href="{{ route('apartados.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                    <i class="fas fa-times mr-2"></i>Limpiar
                </a>
            </div>
        </form>
    </x-card>

    <!-- Lista de apartados -->
    <x-card>
        @if($apartados->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Apartado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Libros</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unidades</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($apartados as $apartado)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $apartado->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $apartado->fecha_apartado->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $apartado->descripcion ?: 'Sin descripción' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $apartado->getTotalLibros() }} libros
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $apartado->getTotalUnidades() }} unidades
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $apartado->getBadgeColor() }}">
                                        <i class="{{ $apartado->getIcon() }} mr-1"></i>
                                        {{ $apartado->getEstadoLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="{{ route('apartados.show', $apartado) }}" 
                                       class="text-blue-600 hover:text-blue-900"
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($apartado->estado === 'activo')
                                        <a href="{{ route('apartados.edit', $apartado) }}" 
                                           class="text-yellow-600 hover:text-yellow-900"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('apartados.completar', $apartado) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('¿Completar este apartado? Esto indica que se vendió todo el inventario apartado.')">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-green-600 hover:text-green-900"
                                                    title="Completar">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('apartados.cancelar', $apartado) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('¿Cancelar este apartado? El inventario se devolverá.')">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Cancelar">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($apartado->estado !== 'completado')
                                        <form action="{{ route('apartados.destroy', $apartado) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('¿Eliminar este apartado?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4">
                {{ $apartados->links() }}
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-box-open text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg">No hay apartados registrados</p>
                <a href="{{ route('apartados.create') }}" 
                   class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Crear primer apartado
                </a>
            </div>
        @endif
    </x-card>
</x-page-layout>
@endsection
