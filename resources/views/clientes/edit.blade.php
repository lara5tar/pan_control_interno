@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('page-title', 'Editar Cliente')
@section('page-description', 'Actualiza la información del cliente')

@section('content')
<x-page-layout 
    title="Editar Cliente"
    :description="'Actualiza la información de: ' . $cliente->nombre"
    button-text="Volver a Clientes"
    button-icon="fas fa-arrow-left"
    :button-route="route('clientes.index')"
>
    <x-card>
        <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user text-gray-400"></i> Nombre <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="nombre"
                        name="nombre" 
                        value="{{ old('nombre', $cliente->nombre) }}"
                        required
                        placeholder="Ej: Juan Pérez"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('nombre') border-red-500 @enderror"
                    >
                    @error('nombre')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-phone text-gray-400"></i> Teléfono (Opcional)
                    </label>
                    <input 
                        type="text" 
                        id="telefono"
                        name="telefono" 
                        value="{{ old('telefono', $cliente->telefono) }}"
                        placeholder="Ej: 1234-5678"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('telefono') border-red-500 @enderror"
                    >
                    @error('telefono')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex flex-col sm:flex-row gap-3 mt-6 pt-6 border-t border-gray-200">
                <x-button type="submit" variant="primary" icon="fas fa-save">
                    Actualizar Cliente
                </x-button>

                <x-button 
                    type="button" 
                    variant="secondary" 
                    icon="fas fa-times"
                    onclick="window.location='{{ route('clientes.index') }}'"
                >
                    Cancelar
                </x-button>

                <x-button 
                    type="button" 
                    variant="info" 
                    icon="fas fa-eye"
                    onclick="window.location='{{ route('clientes.show', $cliente->id) }}'"
                >
                    Ver Detalles
                </x-button>
            </div>
        </form>
    </x-card>

    <!-- Información adicional -->
    <x-card title="Información del Cliente" class="mt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-600 mb-1">
                    <i class="fas fa-calendar-plus text-blue-500 mr-2"></i>
                    <strong>Registrado el:</strong> {{ $cliente->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <div>
                <p class="text-gray-600 mb-1">
                    <i class="fas fa-shopping-cart text-green-500 mr-2"></i>
                    <strong>Total de ventas:</strong> {{ $cliente->ventas->count() }} ventas
                </p>
            </div>
        </div>
        
        @if($cliente->ventas->count() > 0)
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Este cliente tiene <strong>{{ $cliente->ventas->count() }} ventas asociadas</strong>. No se puede eliminar.
                </p>
            </div>
        @endif
    </x-card>
</x-page-layout>
@endsection
