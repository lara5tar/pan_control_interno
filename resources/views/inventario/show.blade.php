@extends('layouts.app')

@section('title', 'Detalle del Libro')

@section('page-title', 'Detalle del Libro')
@section('page-description', 'Información completa del libro')

@section('content')
<x-page-layout 
    title="Detalle del Libro"
    :description="'Información completa de: ' . $libro->nombre"
    button-text="Volver al Inventario"
    button-icon="fas fa-arrow-left"
    :button-route="route('inventario.index')"
>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información principal -->
        <div class="lg:col-span-2">
            <x-card title="Información del Libro" class="h-full">
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
                        <p class="text-lg font-semibold {{ $libro->codigo_barras ? 'text-gray-800' : 'text-gray-400 italic' }}">
                            {{ $libro->codigo_barras ?? 'Sin código de barras' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Precio</p>
                        <p class="text-lg font-semibold text-green-600">${{ number_format($libro->precio, 2) }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Stock Disponible</p>
                        <p class="text-lg font-semibold">
                            <span class="px-3 py-1 rounded-full text-sm {{ $libro->stock > 10 ? 'bg-green-100 text-green-800' : ($libro->stock > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $libro->stock }} unidades
                            </span>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Valor Total en Stock</p>
                        <p class="text-lg font-semibold text-gray-800">${{ number_format($libro->precio * $libro->stock, 2) }}</p>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Código QR -->
        <div class="lg:col-span-1">
            <x-card title="Código QR" class="h-full">
                @if($libro->codigo_barras)
                    <div class="flex flex-col items-center justify-center h-full">
                        <div class="bg-white p-4 rounded-lg border-2 border-gray-200 mb-4">
                            {!! QrCode::size(180)->generate($libro->codigo_barras) !!}
                        </div>
                        <p class="text-sm text-gray-600 text-center mb-3">
                            Código: <span class="font-mono font-semibold text-gray-800">{{ $libro->codigo_barras }}</span>
                        </p>
                        <a href="{{ route('inventario.qr.download', $libro->id) }}" class="w-full">
                            <x-button variant="primary" icon="fas fa-download" class="w-full justify-center">
                                Descargar QR
                            </x-button>
                        </a>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-full text-center">
                        <i class="fas fa-barcode text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500 text-sm font-medium">Sin código de barras</p>
                        <p class="text-gray-400 text-xs mt-1">No disponible</p>
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <!-- Información de fechas y acciones -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="Información de Registro">
            <div class="space-y-4">
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">
                        <i class="fas fa-calendar-plus text-blue-500 mr-2"></i>
                        Fecha de Registro
                    </span>
                    <span class="text-gray-800 font-semibold">
                        {{ $libro->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-gray-600 font-medium">
                        <i class="fas fa-clock text-green-500 mr-2"></i>
                        Última Actualización
                    </span>
                    <span class="text-gray-800 font-semibold">
                        {{ $libro->updated_at->format('d/m/Y H:i') }}
                    </span>
                </div>
            </div>
        </x-card>

        <!-- Acciones -->
        <x-card title="Acciones">
            <div class="space-y-3">
                <x-button variant="primary" icon="fas fa-edit" onclick="window.location='{{ route('inventario.edit', $libro->id) }}'" class="w-full justify-center">
                    Editar
                </x-button>
                
                <x-button variant="secondary" icon="fas fa-arrow-left" onclick="window.location='{{ route('inventario.index') }}'" class="w-full justify-center">
                    Volver al Listado
                </x-button>
                
                <form action="{{ route('inventario.destroy', $libro->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" icon="fas fa-trash" onclick="return confirm('¿Estás seguro de eliminar este libro?')" class="w-full justify-center">
                        Eliminar
                    </x-button>
                </form>
            </div>
        </x-card>
    </div>
</x-page-layout>
@endsection
