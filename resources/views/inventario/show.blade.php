@extends('layouts.app')

@section('title', 'Detalle del Libro')

@section('page-title', 'Detalle del Libro')
@section('page-description', 'Información completa del libro')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Encabezado -->
    <x-page-header 
        title="Detalle del Libro"
        :description="'Información completa de: ' . $libro->nombre"
        button-text="Volver al Inventario"
        button-icon="fas fa-arrow-left"
        :button-route="route('inventario.index')"
    />

    <!-- Información principal -->
    <x-card title="Información del Libro">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-600 mb-1">ID</p>
                <p class="text-lg font-semibold text-gray-800">{{ $libro->id }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-600 mb-1">Nombre</p>
                <p class="text-lg font-semibold text-gray-800">{{ $libro->nombre }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-600 mb-1">Código de Barras</p>
                <p class="text-lg font-semibold text-gray-800">{{ $libro->codigo_barras }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-600 mb-1">Precio</p>
                <p class="text-lg font-semibold text-green-600">${{ number_format($libro->precio, 2) }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-600 mb-1">Stock Disponible</p>
                <p class="text-lg font-semibold">
                    <span class="px-3 py-1 rounded-full {{ $libro->stock > 10 ? 'bg-green-100 text-green-800' : ($libro->stock > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ $libro->stock }} unidades
                    </span>
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-600 mb-1">Valor Total en Stock</p>
                <p class="text-lg font-semibold text-primary-600">${{ number_format($libro->precio * $libro->stock, 2) }}</p>
            </div>
        </div>
    </x-card>

    <!-- Código QR -->
    <x-card title="Código QR">
        <div class="flex flex-col md:flex-row gap-6 items-center">
            <div class="flex-shrink-0">
                <div class="bg-white p-4 rounded-lg border-2 border-gray-200 inline-block">
                    {!! QrCode::size(200)->generate($libro->codigo_barras) !!}
                </div>
            </div>
            <div class="flex-1 text-center md:text-left">
                <h4 class="text-lg font-semibold text-gray-800 mb-2">
                    <i class="fas fa-qrcode text-gray-600"></i>
                    Código QR del Libro
                </h4>
                <p class="text-gray-600 mb-4">
                    Escanea este código QR para acceder rápidamente al código de barras: 
                    <span class="font-mono font-semibold text-gray-800">{{ $libro->codigo_barras }}</span>
                </p>
                <div class="flex justify-center md:justify-start">
                    <a href="{{ route('inventario.qr.download', $libro->id) }}">
                        <x-button variant="primary" icon="fas fa-download">
                            Descargar QR
                        </x-button>
                    </a>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Información de fechas -->
    <x-card title="Registro">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-600 mb-1">Fecha de Creación</p>
                <p class="text-gray-800">
                    <i class="fas fa-calendar mr-2"></i>
                    {{ $libro->created_at->format('d/m/Y H:i:s') }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-600 mb-1">Última Actualización</p>
                <p class="text-gray-800">
                    <i class="fas fa-clock mr-2"></i>
                    {{ $libro->updated_at->format('d/m/Y H:i:s') }}
                </p>
            </div>
        </div>
    </x-card>

    <!-- Acciones -->
    <div class="flex gap-3">
        <x-button variant="primary" icon="fas fa-edit" onclick="window.location='{{ route('inventario.edit', $libro->id) }}'">
            Editar
        </x-button>
        {{-- TEMPORAL: Botón de Movimientos oculto --}}
        {{-- <x-button variant="secondary" icon="fas fa-exchange-alt" onclick="window.location='{{ route('movimientos.index', ['libro_id' => $libro->id]) }}'">
            Ver Movimientos
        </x-button> --}}
        <form action="{{ route('inventario.destroy', $libro->id) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <x-button type="submit" variant="danger" icon="fas fa-trash" onclick="return confirm('¿Estás seguro de eliminar este libro?')">
                Eliminar
            </x-button>
        </form>
    </div>
</div>
@endsection
