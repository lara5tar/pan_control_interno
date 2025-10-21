@extends('layouts.app')

@section('title', 'Movimientos de Inventario')

@section('page-title', 'Movimientos de Inventario')
@section('page-description', 'Historial completo de entradas y salidas')

@section('content')
<div class="space-y-6">
    <!-- Encabezado con botón -->
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">Historial de Movimientos</h3>
            <p class="text-gray-600 text-sm mt-1">Total: {{ $movimientos->total() }} movimientos</p>
        </div>
        <x-button 
            variant="primary" 
            icon="fas fa-plus-circle"
            onclick="window.location='{{ route('movimientos.create') }}'"
        >
            Registrar Movimiento
        </x-button>
    </div>

    <!-- Filtros -->
    <x-card class="overflow-visible">
        <form method="GET" action="{{ route('movimientos.index') }}" class="overflow-visible">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[1fr_auto_auto_auto] gap-4 mb-4 overflow-visible items-end">
                <!-- Filtro por Libro -->
                <div>
                    <x-libro-search-filter 
                        name="libro_id" 
                        :libros="$libros"
                        :selected="request('libro_id')"
                        label="Libro"
                    />
                </div>

                <!-- Filtro por Tipo de Movimiento Unificado -->
                <div class="w-full md:w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-exchange-alt text-gray-400"></i> Tipo de Movimiento
                    </label>
                    <select name="tipo_movimiento" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos los movimientos</option>
                        <option value="entrada" {{ request('tipo_movimiento') == 'entrada' && !str_starts_with(request('tipo_movimiento'), 'entrada_') ? 'selected' : '' }}>
                            ↑ Entrada (general)
                        </option>
                        @foreach(\App\Models\Movimiento::tiposEntrada() as $key => $label)
                            <option value="entrada_{{ $key }}" {{ request('tipo_movimiento') == 'entrada_'.$key ? 'selected' : '' }}>
                                ↑ {{ $label }}
                            </option>
                        @endforeach
                        <option value="salida" {{ request('tipo_movimiento') == 'salida' && !str_starts_with(request('tipo_movimiento'), 'salida_') ? 'selected' : '' }}>
                            ↓ Salida (general)
                        </option>
                        @foreach(\App\Models\Movimiento::tiposSalida() as $key => $label)
                            <option value="salida_{{ $key }}" {{ request('tipo_movimiento') == 'salida_'.$key ? 'selected' : '' }}>
                                ↓ {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Fecha Desde -->
                <div class="w-full md:w-40">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-gray-400"></i> Fecha Desde
                    </label>
                    <input type="date" 
                           name="fecha_desde" 
                           value="{{ request('fecha_desde') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Filtro Fecha Hasta -->
                <div class="w-full md:w-40">
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
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <x-button type="submit" variant="primary" icon="fas fa-filter">
                    Aplicar Filtros
                </x-button>

                @if(request()->hasAny(['libro_id', 'tipo_movimiento', 'tipo_especifico', 'fecha_desde', 'fecha_hasta']))
                    <x-button type="button" variant="secondary" icon="fas fa-times" 
                              onclick="window.location='{{ route('movimientos.index') }}'">
                        Limpiar Filtros
                    </x-button>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Alertas -->
    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('warning'))
        <x-alert type="warning">{{ session('warning') }}</x-alert>
    @endif

    <!-- Tabla de movimientos -->
    <x-card>
        <x-table 
            :headers="['Fecha', 'Libro', 'Tipo', 'Movimiento', 'Cantidad', 'Usuario', 'Acciones']"
            :items="$movimientos"
            emptyMessage="No hay movimientos registrados"
            emptyIcon="fas fa-exchange-alt"
        >
            @foreach($movimientos as $movimiento)
                <x-table-row>
                    <x-table-cell>
                        <div class="text-sm">
                            <div class="font-medium text-gray-900">{{ $movimiento->created_at->format('d/m/Y') }}</div>
                            <div class="text-gray-500">{{ $movimiento->created_at->format('H:i') }}</div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-sm">
                            <div class="font-medium text-gray-900">{{ $movimiento->libro->nombre }}</div>
                            <div class="text-gray-500 text-xs">{{ $movimiento->libro->codigo_barras }}</div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $movimiento->getBadgeColor() }}">
                            <i class="{{ $movimiento->getIcon() }}"></i>
                            {{ $movimiento->getTipoLabel() }}
                        </span>
                    </x-table-cell>
                    <x-table-cell align="center">
                        @if($movimiento->tipo_movimiento === 'entrada')
                            <div class="flex items-center justify-center">
                                <span class="text-green-600 font-bold flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> Entrada
                                </span>
                            </div>
                        @else
                            <div class="flex items-center justify-center">
                                <span class="text-red-600 font-bold flex items-center">
                                    <i class="fas fa-arrow-down mr-1"></i> Salida
                                </span>
                            </div>
                        @endif
                    </x-table-cell>
                    <x-table-cell align="center">
                        <span class="text-lg font-bold {{ $movimiento->tipo_movimiento === 'entrada' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $movimiento->tipo_movimiento === 'entrada' ? '+' : '-' }}{{ $movimiento->cantidad }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-user-circle mr-2"></i>
                            {{ $movimiento->usuario ?? 'N/A' }}
                        </div>
                    </x-table-cell>
                    <x-table-cell align="center">
                        <a href="{{ route('movimientos.show', $movimiento) }}" 
                           class="text-primary-500 hover:text-primary-700 transition-colors inline-flex items-center"
                           title="Ver detalles">
                            <i class="fas fa-eye mr-1"></i>
                            <span class="text-xs">Ver</span>
                        </a>
                    </x-table-cell>
                </x-table-row>
            @endforeach
        </x-table>

        <!-- Paginación -->
        @if($movimientos->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $movimientos->appends(request()->query())->links() }}
            </div>
        @endif
    </x-card>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-arrow-up text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Entradas</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $movimientos->where('tipo_movimiento', 'entrada')->sum('cantidad') }}
                    </p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-full">
                    <i class="fas fa-arrow-down text-red-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Salidas</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $movimientos->where('tipo_movimiento', 'salida')->sum('cantidad') }}
                    </p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-exchange-alt text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Movimientos</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $movimientos->total() }}</p>
                </div>
            </div>
        </x-card>
    </div>
</div>
@endsection
