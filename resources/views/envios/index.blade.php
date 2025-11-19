@extends('layouts.app')

@section('title', 'Envíos')

@section('page-title', 'Envíos')
@section('page-description', 'Gestión de envíos a FedEx')

@section('content')
<x-page-layout 
    title="Listado de Envíos"
    description="Total: {{ $envios->total() }} envíos"
>
    <x-slot name="header">
        <x-button 
            variant="primary" 
            icon="fas fa-plus"
            onclick="window.location='{{ route('envios.create') }}'"
        >
            Nuevo Envío
        </x-button>
    </x-slot>

    <!-- Estadísticas de envíos filtrados -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
        <x-stat-card 
            icon="fas fa-shipping-fast"
            label="Total Envíos"
            :value="$estadisticas['total_envios']"
            bg-color="bg-blue-100"
            icon-color="text-blue-600"
        />

        <x-stat-card 
            icon="fas fa-dollar-sign"
            label="Monto Total"
            :value="'$' . number_format($estadisticas['total_monto'], 2)"
            bg-color="bg-green-100"
            icon-color="text-green-600"
        />

        <x-stat-card 
            icon="fas fa-shopping-cart"
            label="Ventas Asociadas"
            :value="$estadisticas['total_ventas_asociadas']"
            bg-color="bg-purple-100"
            icon-color="text-purple-600"
        />

        <x-stat-card 
            icon="fas fa-check-circle"
            label="Pagados"
            :value="$estadisticas['envios_pagados'] ?? 0"
            bg-color="bg-green-100"
            icon-color="text-green-600"
        />

        <x-stat-card 
            icon="fas fa-clock"
            label="Pendientes"
            :value="$estadisticas['envios_pendientes'] ?? 0"
            bg-color="bg-yellow-100"
            icon-color="text-yellow-600"
        />
    </div>

    <!-- Filtros -->
    <x-card class="overflow-visible">
        <form method="GET" action="{{ route('envios.index') }}" class="overflow-visible">
            <!-- Filters Grid -->
            <div class="grid grid-cols-4 gap-4 mb-4 overflow-visible items-end">
                <!-- Filtro por Venta -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-shopping-cart text-gray-400"></i> Venta
                    </label>
                    <select name="venta_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todas las ventas</option>
                        @foreach($ventas as $venta)
                            <option value="{{ $venta->id }}" {{ request('venta_id') == $venta->id ? 'selected' : '' }}>
                                Venta #{{ $venta->id }} - {{ $venta->cliente?->nombre ?? 'Sin cliente' }} - ${{ number_format($venta->total, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Estado -->
                <!-- Filtro Estado de Pago -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-money-bill-wave text-gray-400"></i> Estado
                    </label>
                    <select name="estado_pago" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('estado_pago') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="pagado" {{ request('estado_pago') === 'pagado' ? 'selected' : '' }}>Pagado</option>
                    </select>
                </div>

                <!-- Filtro Fecha Desde -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-gray-400"></i> Fecha Desde
                    </label>
                    <input type="date" 
                           name="fecha_desde" 
                           value="{{ request('fecha_desde') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Filtro Fecha Hasta -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-check text-gray-400"></i> Fecha Hasta
                    </label>
                    <input type="date" 
                           name="fecha_hasta" 
                           value="{{ request('fecha_hasta') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Filtro Monto Mínimo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-dollar-sign text-gray-400"></i> Monto Mínimo
                    </label>
                    <input type="number" 
                           name="monto_min" 
                           value="{{ request('monto_min') }}" 
                           step="0.01"
                           placeholder="0.00"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Filtro Monto Máximo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-dollar-sign text-gray-400"></i> Monto Máximo
                    </label>
                    <input type="number" 
                           name="monto_max" 
                           value="{{ request('monto_max') }}" 
                           step="0.01"
                           placeholder="0.00"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Búsqueda general -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search text-gray-400"></i> Buscar
                    </label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="ID, Guía..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Ordenar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort text-gray-400"></i> Ordenar por
                    </label>
                    <select name="ordenar" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="reciente" {{ request('ordenar') === 'reciente' ? 'selected' : '' }}>Más reciente</option>
                        <option value="antiguo" {{ request('ordenar') === 'antiguo' ? 'selected' : '' }}>Más antiguo</option>
                        <option value="monto_mayor" {{ request('ordenar') === 'monto_mayor' ? 'selected' : '' }}>Mayor monto</option>
                        <option value="monto_menor" {{ request('ordenar') === 'monto_menor' ? 'selected' : '' }}>Menor monto</option>
                        <option value="guia" {{ request('ordenar') === 'guia' ? 'selected' : '' }}>Guía</option>
                    </select>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex flex-wrap justify-between items-center gap-3 pt-2">
                <div class="flex gap-3">
                    <x-button type="submit" variant="primary" icon="fas fa-filter">
                        Aplicar Filtros
                    </x-button>

                    @if(request()->hasAny(['venta_id', 'estado', 'estado_pago', 'fecha_desde', 'fecha_hasta', 'monto_min', 'monto_max', 'search', 'ordenar']))
                        <x-button type="button" variant="secondary" icon="fas fa-times" 
                                  onclick="window.location='{{ route('envios.index') }}'">
                            Limpiar Filtros
                        </x-button>
                    @endif
                </div>

                <!-- Botones de exportación -->
                <div class="flex gap-3">
                    <x-button 
                        type="button" 
                        variant="success" 
                        icon="fas fa-file-excel"
                        onclick="window.location='{{ route('envios.export.excel', request()->query()) }}'"
                    >
                        Exportar Excel
                    </x-button>
                    
                    <x-button 
                        type="button" 
                        variant="danger" 
                        icon="fas fa-file-pdf"
                        onclick="window.location='{{ route('envios.export.pdf', request()->query()) }}'"
                    >
                        Exportar PDF
                    </x-button>
                </div>
            </div>
        </form>
    </x-card>

    <!-- Tabla de envíos -->
    <x-card>
        <x-data-table 
            :headers="['ID', 'Guía', 'Fecha', 'Ventas', 'Monto a Pagar', 'Estado', 'Acciones']"
            :rows="$envios"
            emptyMessage="No se encontraron envíos"
            emptyIcon="fas fa-shipping-fast"
            :showActions="false"
        >
            @foreach($envios as $envio)
                <tr class="hover:bg-gray-50 transition-colors">
                    <!-- ID -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        #{{ $envio->id }}
                    </td>

                    <!-- Guía -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($envio->guia)
                            <span class="font-medium">{{ $envio->guia }}</span>
                        @else
                            <span class="text-gray-400 italic">Sin guía</span>
                        @endif
                    </td>

                    <!-- Fecha -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <i class="fas fa-calendar-alt text-gray-400 mr-1"></i>
                        {{ $envio->fecha_envio->format('d/m/Y') }}
                    </td>

                    <!-- Ventas -->
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <div class="flex flex-col">
                            <span class="font-medium">{{ $envio->ventas->count() }} ventas</span>
                            @if($envio->ventas->count() > 0)
                                <span class="text-xs text-gray-500">
                                    {{ $envio->total_libros }} libros totales
                                </span>
                            @endif
                        </div>
                    </td>

                    <!-- Monto a Pagar -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                        ${{ number_format($envio->monto_a_pagar, 2) }}
                    </td>

                    <!-- Estado -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $envio->getBadgeColor() }}">
                            <i class="{{ $envio->getIcon() }} mr-1.5"></i>
                            {{ $envio->getEstadoLabel() }}
                        </span>
                    </td>

                    <!-- Acciones -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex justify-end gap-1">
                            <x-button 
                                variant="primary" 
                                size="sm" 
                                icon="fas fa-eye"
                                onclick="window.location='{{ route('envios.show', $envio) }}'"
                                title="Ver detalles">
                            </x-button>
                            
                            <x-button 
                                variant="warning" 
                                size="sm" 
                                icon="fas fa-edit"
                                onclick="window.location='{{ route('envios.edit', $envio) }}'"
                                title="Editar">
                            </x-button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-data-table>

        <!-- Paginación -->
        @if($envios->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $envios->links() }}
            </div>
        @endif
    </x-card>
</x-page-layout>
@endsection
