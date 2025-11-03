@extends('layouts.app')

@section('title', 'Ventas')

@section('page-title', 'Ventas')
@section('page-description', 'Gestión de ventas realizadas')

@section('content')
<x-page-layout 
    title="Listado de Ventas"
    description="Total: {{ $ventas->total() }} ventas"
>
    <x-slot name="header">
        <x-button 
            variant="primary" 
            icon="fas fa-plus"
            onclick="window.location='{{ route('ventas.create') }}'"
        >
            Nueva Venta
        </x-button>
    </x-slot>

    <!-- Estadísticas rápidas -->
    <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.5rem;">
        <x-stat-card 
            icon="fas fa-shopping-cart"
            label="Total Ventas"
            :value="$ventas->total()"
            bg-color="bg-green-100"
            icon-color="text-green-600"
        />

        <x-stat-card 
            icon="fas fa-check-circle"
            label="Completadas"
            :value="$ventas->where('estado', 'completada')->count()"
            bg-color="bg-blue-100"
            icon-color="text-blue-600"
        />

        <x-stat-card 
            icon="fas fa-clock"
            label="Pendientes"
            :value="$ventas->where('estado', 'pendiente')->count()"
            bg-color="bg-yellow-100"
            icon-color="text-yellow-600"
        />

        <x-stat-card 
            icon="fas fa-dollar-sign"
            label="Total Monto"
            :value="'$' . number_format($ventas->sum('total'), 2)"
            bg-color="bg-purple-100"
            icon-color="text-purple-600"
        />
    </div>

    <!-- Filtros y búsqueda -->
    <x-card class="overflow-visible">
        <form method="GET" action="{{ route('ventas.index') }}" class="overflow-visible">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[1fr_auto_auto_auto] gap-4 mb-4 overflow-visible items-end">
                <!-- Búsqueda -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search text-gray-400"></i> Búsqueda
                    </label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Buscar por ID o cliente..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                </div>

                <!-- Filtro por estado -->
                <div class="w-full md:w-40">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-filter text-gray-400"></i> Estado
                    </label>
                    <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos</option>
                        <option value="completada" {{ request('estado') === 'completada' ? 'selected' : '' }}>Completada</option>
                        <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="cancelada" {{ request('estado') === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>

                <!-- Filtro por tipo de pago -->
                <div class="w-full md:w-40">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-credit-card text-gray-400"></i> Tipo Pago
                    </label>
                    <select name="tipo_pago" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos</option>
                        <option value="contado" {{ request('tipo_pago') === 'contado' ? 'selected' : '' }}>Contado</option>
                        <option value="credito" {{ request('tipo_pago') === 'credito' ? 'selected' : '' }}>Crédito</option>
                        <option value="mixto" {{ request('tipo_pago') === 'mixto' ? 'selected' : '' }}>Mixto</option>
                    </select>
                </div>

                <!-- Ordenar por -->
                <div class="w-full md:w-40">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort text-gray-400"></i> Ordenar
                    </label>
                    <select name="ordenar" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="reciente" {{ request('ordenar', 'reciente') == 'reciente' ? 'selected' : '' }}>Más recientes</option>
                        <option value="antiguo" {{ request('ordenar') == 'antiguo' ? 'selected' : '' }}>Más antiguas</option>
                        <option value="monto_mayor" {{ request('ordenar') == 'monto_mayor' ? 'selected' : '' }}>Mayor monto</option>
                        <option value="monto_menor" {{ request('ordenar') == 'monto_menor' ? 'selected' : '' }}>Menor monto</option>
                    </select>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex flex-wrap justify-between items-center gap-3 pt-2">
                <div class="flex gap-3">
                    <x-button type="submit" variant="primary" icon="fas fa-filter">
                        Aplicar Filtros
                    </x-button>

                    @if(request()->hasAny(['search', 'estado', 'tipo_pago', 'ordenar']))
                        <x-button type="button" variant="secondary" icon="fas fa-times" 
                                  onclick="window.location='{{ route('ventas.index') }}'">
                            Limpiar Filtros
                        </x-button>
                    @endif
                </div>
            </div>
        </form>
    </x-card>

    <!-- Tabla de ventas -->
    <x-card>
        <x-data-table 
            :headers="['ID', 'Cliente', 'Fecha', 'Tipo Pago', 'Total', 'Estado', 'Libros']"
            :rows="$ventas"
            emptyMessage="No hay ventas registradas"
            emptyIcon="fas fa-cash-register"
        >
            @foreach($ventas as $venta)
                <x-data-table-row>
                    <x-data-table-cell bold>#{{ $venta->id }}</x-data-table-cell>
                    <x-data-table-cell>
                        <div class="text-sm">
                            <div class="font-medium text-gray-900">
                                {{ $venta->cliente?->nombre ?: 'Sin cliente' }}
                            </div>
                            <div class="text-gray-500 text-xs">
                                @if($venta->cliente?->telefono)
                                    <i class="fas fa-phone mr-1"></i>{{ $venta->cliente->telefono }}
                                @else
                                    Venta anónima
                                @endif
                            </div>
                        </div>
                    </x-data-table-cell>
                    <x-data-table-cell>{{ $venta->fecha_venta->format('d/m/Y') }}</x-data-table-cell>
                    <x-data-table-cell>
                        <span class="text-sm text-gray-700">
                            <i class="fas fa-credit-card text-gray-400 mr-1"></i>
                            {{ $venta->getTipoPagoLabel() }}
                        </span>
                    </x-data-table-cell>
                    <x-data-table-cell>
                        @if($venta->descuento_global > 0)
                            <div class="text-xs text-gray-500 line-through">
                                ${{ number_format($venta->subtotal, 2) }}
                            </div>
                            <div class="font-bold text-green-600">
                                ${{ number_format($venta->total, 2) }}
                            </div>
                        @else
                            <div class="font-bold text-gray-900">
                                ${{ number_format($venta->total, 2) }}
                            </div>
                        @endif
                    </x-data-table-cell>
                    <x-data-table-cell>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $venta->getBadgeColor() }}">
                            <i class="{{ $venta->getIcon() }} mr-1"></i>
                            {{ $venta->getEstadoLabel() }}
                        </span>
                    </x-data-table-cell>
                    <x-data-table-cell>
                        <span class="inline-flex items-center px-2 py-1 bg-gray-100 rounded-md text-sm">
                            <i class="fas fa-book text-gray-500 mr-1"></i>
                            {{ $venta->movimientos->count() }}
                        </span>
                    </x-data-table-cell>
                    <x-data-table-cell>
                        <div class="flex justify-end gap-2">
                            <x-button 
                                href="{{ route('ventas.show', $venta) }}" 
                                variant="primary" 
                                size="sm"
                                icon="fas fa-eye"
                                title="Ver detalles">
                            </x-button>
                            
                            @if($venta->estado === 'completada')
                                <form action="{{ route('ventas.cancelar', $venta) }}" method="POST" class="inline">
                                    @csrf
                                    <x-button 
                                        type="submit" 
                                        variant="warning" 
                                        size="sm"
                                        icon="fas fa-ban"
                                        title="Cancelar venta"
                                        onclick="return confirm('¿Estás seguro de cancelar esta venta? Se restaurará el stock.')">
                                    </x-button>
                                </form>
                            @endif
                        </div>
                    </x-data-table-cell>
                </x-data-table-row>
            @endforeach
        </x-data-table>

        <!-- Paginación -->
        @if($ventas->hasPages())
            <div class="mt-4 px-6 py-4 border-t border-gray-200">
                {{ $ventas->appends(request()->query())->links() }}
            </div>
        @endif
    </x-card>
</x-page-layout>
@endsection
