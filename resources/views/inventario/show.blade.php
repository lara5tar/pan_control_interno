@extends('layouts.app')

@section('title', 'Detalle del Libro')

@section('page-title', 'Detalle del Libro')
@section('page-description', 'Información completa del libro')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
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
        <x-button variant="secondary" icon="fas fa-arrow-left" onclick="window.location='{{ route('inventario.index') }}'">
            Volver al Listado
        </x-button>
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
