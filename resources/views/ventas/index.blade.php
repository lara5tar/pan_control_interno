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

    <!-- Estadísticas de ventas filtradas -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
        <x-stat-card 
            icon="fas fa-shopping-cart"
            label="Total Ventas"
            :value="$estadisticas['total_ventas']"
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
            icon="fas fa-hand-holding-usd"
            label="Total Pagado"
            :value="'$' . number_format($estadisticas['total_pagado'], 2)"
            bg-color="bg-teal-100"
            icon-color="text-teal-600"
        />

        <x-stat-card 
            icon="fas fa-exclamation-circle"
            label="Saldo Pendiente"
            :value="'$' . number_format($estadisticas['total_pendiente'], 2)"
            bg-color="bg-orange-100"
            icon-color="text-orange-600"
        />

        <x-stat-card 
            icon="fas fa-check-circle"
            label="Completadas"
            :value="$estadisticas['ventas_completadas']"
            bg-color="bg-green-100"
            icon-color="text-green-600"
        />

        @if($estadisticas['ventas_vencidas'] > 0)
        <x-stat-card 
            icon="fas fa-clock"
            label="Vencidas"
            :value="$estadisticas['ventas_vencidas']"
            bg-color="bg-red-100"
            icon-color="text-red-600"
        />
        @endif

        @if($estadisticas['ventas_canceladas'] > 0)
        <x-stat-card 
            icon="fas fa-ban"
            label="Canceladas"
            :value="$estadisticas['ventas_canceladas']"
            bg-color="bg-gray-100"
            icon-color="text-gray-600"
        />
        @endif
    </div>

    <!-- Filtros -->
    <x-card class="overflow-visible">
        <form method="GET" action="{{ route('ventas.index') }}" class="overflow-visible">
            <!-- Filters Grid -->
            <div class="grid grid-cols-4 gap-4 mb-4 overflow-visible">
                <!-- Filtro por Cliente - Ocupa toda la primera fila -->
                <div class="col-span-4">
                    @php
                        $selectedClienteId = request('cliente_id');
                        $selectedCliente = $selectedClienteId ? \App\Models\Cliente::find($selectedClienteId) : null;
                    @endphp
                    <x-cliente-search-dynamic 
                        name="cliente_id"
                        :selected="$selectedClienteId"
                        :clienteData="$selectedCliente"
                        label="Cliente"
                        :required="false"
                        placeholder="Buscar o seleccionar cliente..."
                    />
                </div>

                <!-- Filtro por Libro - Ocupa toda la segunda fila -->
                <div class="col-span-4">
                    @php
                        $selectedLibroId = request('libro_id');
                    @endphp
                    <x-libro-search-dynamic 
                        name="libro_id"
                        :selected="$selectedLibroId"
                        :libros="$libros"
                        label="Libro"
                        :required="false"
                    />
                </div>

                <!-- Filtro por Estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-info-circle text-gray-400"></i> Estado
                    </label>
                    <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos los estados</option>
                        <option value="completada" {{ request('estado') === 'completada' ? 'selected' : '' }}>Completada</option>
                        <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="cancelada" {{ request('estado') === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>

                <!-- Filtro por Tipo de Pago -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-credit-card text-gray-400"></i> Tipo de Pago
                    </label>
                    <select name="tipo_pago" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos</option>
                        <option value="contado" {{ request('tipo_pago') === 'contado' ? 'selected' : '' }}>Contado</option>
                        <option value="credito" {{ request('tipo_pago') === 'credito' ? 'selected' : '' }}>Crédito</option>
                        <option value="mixto" {{ request('tipo_pago') === 'mixto' ? 'selected' : '' }}>Mixto</option>
                    </select>
                </div>

                <!-- Filtro por Estado de Pago -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-money-check-alt text-gray-400"></i> Estado de Pago
                    </label>
                    <select name="estado_pago" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('estado_pago') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="parcial" {{ request('estado_pago') === 'parcial' ? 'selected' : '' }}>Pago Parcial</option>
                        <option value="completado" {{ request('estado_pago') === 'completado' ? 'selected' : '' }}>Completado</option>
                    </select>
                </div>

                <!-- Filtro Ventas Vencidas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-exclamation-triangle text-gray-400"></i> Ventas Vencidas
                    </label>
                    <select name="vencidas" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todas</option>
                        <option value="1" {{ request('vencidas') === '1' ? 'selected' : '' }}>Solo vencidas</option>
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
            </div>

            <!-- Botones de acción -->
            <div class="flex flex-wrap justify-between items-center gap-3 pt-2">
                <div class="flex gap-3">
                    <x-button type="submit" variant="primary" icon="fas fa-filter">
                        Aplicar Filtros
                    </x-button>

                    @if(request()->hasAny(['cliente_id', 'estado', 'tipo_pago', 'estado_pago', 'libro_id', 'fecha_desde', 'fecha_hasta', 'vencidas']))
                        <x-button type="button" variant="secondary" icon="fas fa-times" 
                                  onclick="window.location='{{ route('ventas.index') }}'">
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
                        onclick="window.location='{{ route('ventas.export.excel', request()->query()) }}'"
                    >
                        Exportar Excel
                    </x-button>
                    
                    <x-button 
                        type="button" 
                        variant="danger" 
                        icon="fas fa-file-pdf"
                        onclick="window.location='{{ route('ventas.export.pdf', request()->query()) }}'"
                    >
                        Exportar PDF
                    </x-button>
                </div>
            </div>
        </form>
    </x-card>

    <!-- Tabla de ventas -->
    <x-card>
        <x-data-table 
            :headers="['ID', 'Fecha', 'Cliente', 'Total', 'Pagos / Saldo', 'Estado', 'Acciones']"
            :rows="$ventas"
            emptyMessage="No se encontraron ventas"
            emptyIcon="fas fa-shopping-cart"
            :showActions="false"
        >
            @foreach($ventas as $venta)
                <x-data-table-row>
                    <!-- ID -->
                    <x-data-table-cell bold>
                        <div class="flex items-center gap-1">
                            #{{ $venta->id }}
                            @if($venta->tiene_envio)
                                <span class="text-xs px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded" title="Tiene envío asignado">
                                    <i class="fas fa-shipping-fast"></i>
                                </span>
                            @endif
                        </div>
                    </x-data-table-cell>

                    <!-- Fecha -->
                    <x-data-table-cell>
                        <div class="text-sm">
                            <div class="font-medium text-gray-900">
                                {{ $venta->fecha_venta->format('d/m/Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $venta->fecha_venta->format('H:i') }}
                            </div>
                        </div>
                    </x-data-table-cell>

                    <!-- Cliente -->
                    <x-data-table-cell>
                        <div class="text-sm">
                            <div class="font-medium text-gray-900">
                                {{ $venta->cliente?->nombre ?: 'Sin cliente' }}
                            </div>
                            @if($venta->cliente?->telefono)
                                <div class="text-gray-500 text-xs">
                                    <i class="fas fa-phone mr-1"></i>{{ $venta->cliente->telefono }}
                                </div>
                            @endif
                            <div class="text-xs text-gray-500 mt-0.5">
                                <i class="fas fa-book mr-1"></i>{{ $venta->movimientos->count() }} libro(s) | 
                                <span class="inline-flex items-center text-xs
                                    {{ $venta->tipo_pago === 'contado' ? 'text-green-600' : '' }}
                                    {{ $venta->tipo_pago === 'credito' ? 'text-orange-600' : '' }}
                                    {{ $venta->tipo_pago === 'mixto' ? 'text-blue-600' : '' }}">
                                    <i class="fas fa-credit-card mr-1"></i>{{ $venta->getTipoPagoLabel() }}
                                </span>
                            </div>
                        </div>
                    </x-data-table-cell>

                    <!-- Total -->
                    <x-data-table-cell>
                        <div class="text-sm">
                            <div class="font-bold text-gray-900 text-base">
                                ${{ number_format($venta->total, 2) }}
                            </div>
                            @if($venta->descuento_global > 0)
                                <div class="text-xs text-yellow-600">
                                    <i class="fas fa-tag mr-1"></i>{{ $venta->descuento_global }}% desc.
                                </div>
                            @endif
                        </div>
                    </x-data-table-cell>

                    <!-- Pagos / Saldo -->
                    <x-data-table-cell>
                        <div class="text-sm">
                            @if($venta->total_pagado > 0)
                                <div class="font-medium text-green-600">
                                    <i class="fas fa-check-circle mr-1"></i>${{ number_format($venta->total_pagado, 2) }}
                                </div>
                            @endif
                            @if($venta->saldo_pendiente > 0)
                                <div class="font-semibold text-orange-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>${{ number_format($venta->saldo_pendiente, 2) }}
                                </div>
                            @elseif($venta->total_pagado === 0)
                                <span class="text-sm text-gray-400">Sin pagos</span>
                            @else
                                <span class="text-sm text-green-500">
                                    <i class="fas fa-check-double mr-1"></i>Pagado
                                </span>
                            @endif
                        </div>
                    </x-data-table-cell>

                    <!-- Estado -->
                    <x-data-table-cell>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $venta->getEstadoUnificadoBadgeColor() }}">
                            <i class="{{ $venta->getEstadoUnificadoIcon() }} mr-1"></i>
                            {{ $venta->getEstadoUnificadoLabel() }}
                        </span>
                    </x-data-table-cell>

                    <!-- Acciones -->
                    <x-data-table-cell>
                        <div class="flex justify-end gap-1">
                            <x-button 
                                href="{{ route('ventas.show', $venta) }}" 
                                variant="primary" 
                                size="sm"
                                icon="fas fa-eye"
                                title="Ver detalles">
                            </x-button>
                            
                            @php
                                $roles = session('roles', []);
                                $isAdmin = false;
                                foreach ($roles as $rol) {
                                    $rolNombre = strtoupper(trim($rol['ROL'] ?? ''));
                                    if ($rolNombre === 'ADMIN LIBRERIA' || $rolNombre === 'ADMIN LIBRERÍA') {
                                        $isAdmin = true;
                                        break;
                                    }
                                }
                            @endphp
                            
                            @if($isAdmin)
                                <x-button 
                                    href="{{ route('ventas.edit', $venta) }}" 
                                    variant="warning" 
                                    size="sm"
                                    icon="fas fa-edit"
                                    title="Editar venta">
                                </x-button>
                            @endif
                            
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

@push('scripts')
<script src="{{ asset('js/cliente-search-dynamic.js') }}"></script>
<script src="{{ asset('js/libro-search-dynamic.js') }}"></script>
@endpush
