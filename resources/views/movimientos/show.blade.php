@extends('layouts.app')

@section('title', 'Detalle del Movimiento')

@section('page-title', 'Detalle del Movimiento')
@section('page-description', 'Información completa del movimiento de inventario')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Información Principal -->
    <x-card>
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">
                    Movimiento #{{ $movimiento->id }}
                </h3>
                <p class="text-gray-600">
                    <i class="fas fa-calendar-alt"></i>
                    {{ $movimiento->created_at->format('d/m/Y H:i:s') }}
                </p>
            </div>
            <div class="text-right">
                @if($movimiento->tipo_movimiento === 'entrada')
                    <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-lg font-bold">
                        <i class="fas fa-arrow-down mr-2 text-xl"></i>
                        ENTRADA
                    </div>
                @else
                    <div class="inline-flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-lg font-bold">
                        <i class="fas fa-arrow-up mr-2 text-xl"></i>
                        SALIDA
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Información del Libro -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-3">
                    <i class="fas fa-book"></i> INFORMACIÓN DEL LIBRO
                </h4>
                <div class="space-y-2">
                    <div>
                        <p class="text-xs text-gray-500">Nombre</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $movimiento->libro->nombre }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Código de Barras</p>
                        <p class="font-mono text-gray-700">{{ $movimiento->libro->codigo_barras }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Stock Actual</p>
                        <p class="text-lg font-bold text-primary-600">
                            <i class="fas fa-boxes"></i> {{ $movimiento->libro->stock }} unidades
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detalles del Movimiento -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-3">
                    <i class="fas fa-info-circle"></i> DETALLES DEL MOVIMIENTO
                </h4>
                <div class="space-y-2">
                    <div>
                        <p class="text-xs text-gray-500">Tipo de Movimiento</p>
                        <span class="inline-block px-3 py-1 rounded-full text-sm font-medium {{ $movimiento->getBadgeColor() }}">
                            <i class="{{ $movimiento->getIcon() }}"></i>
                            {{ $movimiento->getTipoLabel() }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Cantidad</p>
                        <p class="text-2xl font-bold {{ $movimiento->tipo_movimiento === 'entrada' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $movimiento->tipo_movimiento === 'entrada' ? '+' : '-' }}{{ $movimiento->cantidad }} unidades
                        </p>
                    </div>
                    @if($movimiento->precio_unitario)
                        <div>
                            <p class="text-xs text-gray-500">Precio Unitario</p>
                            <p class="text-lg font-semibold text-gray-900">
                                ${{ number_format($movimiento->precio_unitario, 2) }}
                            </p>
                        </div>
                        <div class="pt-2 border-t border-gray-200">
                            <p class="text-xs text-gray-500">Total</p>
                            <p class="text-xl font-bold text-gray-900">
                                ${{ number_format($movimiento->precio_unitario * $movimiento->cantidad, 2) }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Observaciones -->
        @if($movimiento->observaciones)
            <div class="mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                <h4 class="text-sm font-medium text-yellow-800 mb-2">
                    <i class="fas fa-sticky-note"></i> Observaciones
                </h4>
                <p class="text-gray-700">{{ $movimiento->observaciones }}</p>
            </div>
        @endif

        <!-- Información del Usuario -->
        <div class="mt-6 pt-6 border-t border-gray-200 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                <i class="fas fa-user-circle"></i>
                Registrado por: <strong>{{ $movimiento->usuario ?? 'N/A' }}</strong>
            </div>
            <div class="text-sm text-gray-500">
                <i class="fas fa-clock"></i>
                Hace {{ $movimiento->created_at->diffForHumans() }}
            </div>
        </div>
    </x-card>

    <!-- Historial del Libro -->
    <x-card>
        <h3 class="text-lg font-bold text-gray-900 mb-4">
            <i class="fas fa-history"></i> Últimos Movimientos de este Libro
        </h3>
        <div class="space-y-3">
            @foreach($movimiento->libro->movimientos()->orderBy('created_at', 'desc')->take(5)->get() as $mov)
                <div class="flex items-center justify-between p-3 {{ $mov->id === $movimiento->id ? 'bg-primary-50 border-2 border-primary-500' : 'bg-gray-50' }} rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 {{ $mov->tipo_movimiento === 'entrada' ? 'bg-green-100' : 'bg-red-100' }} rounded-full">
                            <i class="{{ $mov->getIcon() }} {{ $mov->tipo_movimiento === 'entrada' ? 'text-green-600' : 'text-red-600' }}"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $mov->getTipoLabel() }}</p>
                            <p class="text-xs text-gray-500">{{ $mov->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold {{ $mov->tipo_movimiento === 'entrada' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $mov->tipo_movimiento === 'entrada' ? '+' : '-' }}{{ $mov->cantidad }}
                        </p>
                        @if($mov->id === $movimiento->id)
                            <span class="text-xs text-primary-600 font-medium">
                                <i class="fas fa-eye"></i> Actual
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>

    <!-- Botones de Acción -->
    <div class="flex gap-3">
        <x-button 
            variant="secondary" 
            icon="fas fa-arrow-left"
            onclick="window.location='{{ route('movimientos.index') }}'"
        >
            Volver al Listado
        </x-button>
        <x-button 
            variant="secondary" 
            icon="fas fa-book"
            onclick="window.location='{{ route('inventario.show', $movimiento->libro) }}'"
        >
            Ver Libro
        </x-button>
    </div>
</div>
@endsection
