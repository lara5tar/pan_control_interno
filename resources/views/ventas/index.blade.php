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

    <!-- Estadísticas de ventas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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
    </div>

    <!-- Estadísticas adicionales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card 
            icon="fas fa-check-circle"
            label="Completadas"
            :value="$estadisticas['ventas_completadas']"
            bg-color="bg-green-100"
            icon-color="text-green-600"
        />

        <x-stat-card 
            icon="fas fa-calendar-alt"
            label="A Plazos"
            :value="$estadisticas['ventas_a_plazos']"
            bg-color="bg-purple-100"
            icon-color="text-purple-600"
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
            
            <!-- Filtros rápidos de periodo -->
            <div class="mb-6 p-4 bg-gradient-to-r from-primary-50 to-blue-50 rounded-lg border border-primary-100">
                <label class="block text-sm font-semibold text-gray-800 mb-3">
                    <i class="fas fa-clock text-primary-600"></i> Periodos Rápidos
                </label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="setFechaHoy()" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-primary-50 hover:border-primary-300 transition-colors">
                        <i class="fas fa-calendar-day"></i> Hoy
                    </button>
                    <button type="button" onclick="setFechaSemana()" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-primary-50 hover:border-primary-300 transition-colors">
                        <i class="fas fa-calendar-week"></i> Esta Semana
                    </button>
                    <button type="button" onclick="setFechaMes()" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-primary-50 hover:border-primary-300 transition-colors">
                        <i class="fas fa-calendar-alt"></i> Este Mes
                    </button>
                    <button type="button" onclick="setFechaUltimos30()" class="px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-primary-50 hover:border-primary-300 transition-colors">
                        <i class="fas fa-calendar"></i> Últimos 30 días
                    </button>
                    <button type="button" onclick="limpiarFechas()" class="px-4 py-2 text-sm bg-white border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                        <i class="fas fa-times"></i> Limpiar Fechas
                    </button>
                </div>
            </div>

            <!-- Rango de fechas y montos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4 overflow-visible items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-gray-400"></i> Fecha Desde
                    </label>
                    <input 
                        type="date" 
                        name="fecha_desde" 
                        id="fecha_desde"
                        value="{{ request('fecha_desde') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-check text-gray-400"></i> Fecha Hasta
                    </label>
                    <input 
                        type="date" 
                        name="fecha_hasta" 
                        id="fecha_hasta"
                        value="{{ request('fecha_hasta') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-dollar-sign text-gray-400"></i> Monto Mínimo
                    </label>
                    <input 
                        type="number" 
                        name="monto_min" 
                        value="{{ request('monto_min') }}"
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-dollar-sign text-gray-400"></i> Monto Máximo
                    </label>
                    <input 
                        type="number" 
                        name="monto_max" 
                        value="{{ request('monto_max') }}"
                        placeholder="9999.99"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                </div>
            </div>

            <!-- Cliente, Libro y Búsqueda -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 overflow-visible items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user text-gray-400"></i> Cliente
                    </label>
                    <select name="cliente_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos los clientes</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-book text-gray-400"></i> Libro Vendido
                    </label>
                    <select name="libro_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos los libros</option>
                        @foreach($libros as $libro)
                            <option value="{{ $libro->id }}" {{ request('libro_id') == $libro->id ? 'selected' : '' }}>
                                {{ $libro->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search text-gray-400"></i> Búsqueda General
                    </label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Buscar por ID, cliente u observaciones..." 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                </div>
            </div>

            <!-- Estados y Tipos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4 overflow-visible items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-info-circle text-gray-400"></i> Estado Venta
                    </label>
                    <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos los estados</option>
                        <option value="completada" {{ request('estado') === 'completada' ? 'selected' : '' }}>Completada</option>
                        <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="cancelada" {{ request('estado') === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>

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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-check text-gray-400"></i> Modalidad
                    </label>
                    <select name="es_a_plazos" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todas las ventas</option>
                        <option value="1" {{ request('es_a_plazos') === '1' ? 'selected' : '' }}>Solo a plazos</option>
                        <option value="0" {{ request('es_a_plazos') === '0' ? 'selected' : '' }}>Solo contado</option>
                    </select>
                </div>

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
            </div>

            <!-- Ventas vencidas y ordenamiento -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 overflow-visible items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-exclamation-triangle text-gray-400"></i> Ventas Vencidas
                    </label>
                    <select name="vencidas" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todas</option>
                        <option value="1" {{ request('vencidas') === '1' ? 'selected' : '' }}>Solo vencidas</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort text-gray-400"></i> Ordenar por
                    </label>
                    <select name="ordenar" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="reciente" {{ request('ordenar', 'reciente') == 'reciente' ? 'selected' : '' }}>Más recientes</option>
                        <option value="antiguo" {{ request('ordenar') == 'antiguo' ? 'selected' : '' }}>Más antiguas</option>
                        <option value="monto_mayor" {{ request('ordenar') == 'monto_mayor' ? 'selected' : '' }}>Mayor monto</option>
                        <option value="monto_menor" {{ request('ordenar') == 'monto_menor' ? 'selected' : '' }}>Menor monto</option>
                        <option value="cliente" {{ request('ordenar') == 'cliente' ? 'selected' : '' }}>Por cliente</option>
                        <option value="saldo_mayor" {{ request('ordenar') == 'saldo_mayor' ? 'selected' : '' }}>Mayor saldo pendiente</option>
                    </select>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex flex-wrap justify-between items-center gap-3 pt-2">
                <div class="flex gap-3">
                    <x-button type="submit" variant="primary" icon="fas fa-filter">
                        Aplicar Filtros
                    </x-button>

                    @if(request()->hasAny(['search', 'estado', 'tipo_pago', 'es_a_plazos', 'estado_pago', 'cliente_id', 'libro_id', 'fecha_desde', 'fecha_hasta', 'monto_min', 'monto_max', 'vencidas', 'ordenar']))
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
                        onclick="alert('Exportación Excel - Próximamente')"
                    >
                        Exportar Excel
                    </x-button>
                    
                    <x-button 
                        type="button" 
                        variant="danger" 
                        icon="fas fa-file-pdf"
                        onclick="alert('Exportación PDF - Próximamente')"
                    >
                        Exportar PDF
                    </x-button>
                </div>
            </div>
        </form>
    </x-card>

    <script>
        function setFechaHoy() {
            const hoy = new Date().toISOString().split('T')[0];
            document.getElementById('fecha_desde').value = hoy;
            document.getElementById('fecha_hasta').value = hoy;
        }

        function setFechaSemana() {
            const hoy = new Date();
            const primerDia = new Date(hoy.setDate(hoy.getDate() - hoy.getDay() + 1));
            const ultimoDia = new Date(hoy.setDate(hoy.getDate() - hoy.getDay() + 7));
            
            document.getElementById('fecha_desde').value = primerDia.toISOString().split('T')[0];
            document.getElementById('fecha_hasta').value = ultimoDia.toISOString().split('T')[0];
        }

        function setFechaMes() {
            const hoy = new Date();
            const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
            
            document.getElementById('fecha_desde').value = primerDia.toISOString().split('T')[0];
            document.getElementById('fecha_hasta').value = ultimoDia.toISOString().split('T')[0];
        }

        function setFechaUltimos30() {
            const hoy = new Date();
            const hace30 = new Date(hoy.setDate(hoy.getDate() - 30));
            
            document.getElementById('fecha_desde').value = hace30.toISOString().split('T')[0];
            document.getElementById('fecha_hasta').value = new Date().toISOString().split('T')[0];
        }

        function limpiarFechas() {
            document.getElementById('fecha_desde').value = '';
            document.getElementById('fecha_hasta').value = '';
        }
    </script>

    <!-- Tabla de ventas -->
    <x-card>
        <x-data-table 
            :headers="['ID', 'Fecha', 'Cliente', 'Libros', 'Tipo Pago', 'Subtotal', 'Descuento', 'Total', 'Pagado', 'Saldo', 'Estado', 'Acciones']"
            :rows="$ventas"
            emptyMessage="No se encontraron ventas con los filtros aplicados"
            emptyIcon="fas fa-search"
        >
            @foreach($ventas as $venta)
                <x-data-table-row class="{{ $venta->es_a_plazos && $venta->estado_pago !== 'completado' && $venta->fecha_limite && $venta->fecha_limite->isPast() ? 'bg-red-50' : '' }}">
                    <!-- ID -->
                    <x-data-table-cell bold>
                        <div class="flex items-center gap-1">
                            #{{ $venta->id }}
                            @if($venta->es_a_plazos)
                                <span class="text-xs px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded" title="Venta a plazos">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                            @endif
                            @if($venta->es_a_plazos && $venta->estado_pago !== 'completado' && $venta->fecha_limite && $venta->fecha_limite->isPast())
                                <span class="text-xs px-1.5 py-0.5 bg-red-100 text-red-700 rounded" title="Vencida">
                                    <i class="fas fa-exclamation-triangle"></i>
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
                            @if($venta->es_a_plazos && $venta->fecha_limite)
                                <div class="text-xs {{ $venta->fecha_limite->isPast() && $venta->estado_pago !== 'completado' ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                    <i class="fas fa-clock"></i> Vence: {{ $venta->fecha_limite->format('d/m/Y') }}
                                </div>
                            @endif
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
                        </div>
                    </x-data-table-cell>

                    <!-- Libros -->
                    <x-data-table-cell>
                        <div class="text-sm">
                            <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 rounded-md font-medium">
                                <i class="fas fa-book mr-1"></i>
                                {{ $venta->movimientos->count() }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $venta->movimientos->sum('cantidad') }} unidades
                            </div>
                        </div>
                    </x-data-table-cell>

                    <!-- Tipo Pago -->
                    <x-data-table-cell>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                            {{ $venta->tipo_pago === 'contado' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $venta->tipo_pago === 'credito' ? 'bg-orange-100 text-orange-700' : '' }}
                            {{ $venta->tipo_pago === 'mixto' ? 'bg-blue-100 text-blue-700' : '' }}">
                            <i class="fas fa-credit-card mr-1"></i>
                            {{ $venta->getTipoPagoLabel() }}
                        </span>
                    </x-data-table-cell>

                    <!-- Subtotal -->
                    <x-data-table-cell>
                        <div class="text-sm font-medium text-gray-700">
                            ${{ number_format($venta->subtotal, 2) }}
                        </div>
                    </x-data-table-cell>

                    <!-- Descuento -->
                    <x-data-table-cell>
                        @if($venta->descuento_global > 0)
                            <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-medium">
                                <i class="fas fa-percent mr-1"></i>
                                {{ $venta->descuento_global }}%
                            </span>
                            <div class="text-xs text-gray-500 mt-1">
                                -${{ number_format($venta->subtotal - $venta->total, 2) }}
                            </div>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </x-data-table-cell>

                    <!-- Total -->
                    <x-data-table-cell>
                        <div class="font-bold text-gray-900 text-base">
                            ${{ number_format($venta->total, 2) }}
                        </div>
                    </x-data-table-cell>

                    <!-- Pagado -->
                    <x-data-table-cell>
                        <div class="text-sm">
                            <div class="font-medium {{ $venta->total_pagado > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                ${{ number_format($venta->total_pagado, 2) }}
                            </div>
                            @if($venta->es_a_plazos && $venta->total_pagado > 0 && $venta->total_pagado < $venta->total)
                                <div class="text-xs text-gray-500">
                                    {{ number_format(($venta->total_pagado / $venta->total) * 100, 1) }}%
                                </div>
                            @endif
                        </div>
                    </x-data-table-cell>

                    <!-- Saldo -->
                    <x-data-table-cell>
                        @if($venta->saldo_pendiente > 0)
                            <div class="font-semibold text-orange-600">
                                ${{ number_format($venta->saldo_pendiente, 2) }}
                            </div>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
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
                            
                            @if($venta->es_a_plazos && $venta->estado_pago !== 'completado' && $venta->estado !== 'cancelada')
                                <x-button 
                                    href="{{ route('ventas.pagos.create', $venta) }}" 
                                    variant="info" 
                                    size="sm"
                                    icon="fas fa-hand-holding-usd"
                                    title="Registrar pago">
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
