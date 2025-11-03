@extends('layouts.app')

@section('title', 'Agregar Cliente')

@section('page-title', 'Agregar Nuevo Cliente')
@section('page-description', 'Registra un nuevo cliente')

@section('content')
<x-page-layout 
    title="Registrar Nuevo Cliente"
    description="Agrega un nuevo cliente al sistema"
    button-text="Volver a Clientes"
    button-icon="fas fa-arrow-left"
    :button-route="route('clientes.index')"
>
    <x-card>
        <form action="{{ route('clientes.store') }}" method="POST">
            @csrf
            
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
                        value="{{ old('nombre') }}"
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
                        value="{{ old('telefono') }}"
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
                    Guardar Cliente
                </x-button>

                <x-button 
                    type="button" 
                    variant="secondary" 
                    icon="fas fa-times"
                    onclick="window.location='{{ route('clientes.index') }}'"
                >
                    Cancelar
                </x-button>
            </div>
        </form>
    </x-card>

    <!-- Información adicional -->
    <x-card title="Información" class="mt-6">
        <div class="text-sm text-gray-600 space-y-2">
            <p><i class="fas fa-info-circle text-blue-500 mr-2"></i> El campo <strong>Nombre</strong> es obligatorio.</p>
            <p><i class="fas fa-info-circle text-blue-500 mr-2"></i> El <strong>Teléfono</strong> es opcional y puede agregarse o editarse después.</p>
            <p><i class="fas fa-info-circle text-blue-500 mr-2"></i> Podrás asociar este cliente a ventas una vez registrado.</p>
        </div>
    </x-card>
</x-page-layout>

@if(session('nuevo_cliente_id'))
<script>
    // Guardar el cliente recién creado en sessionStorage para el formulario de ventas
    sessionStorage.setItem('nuevo_cliente_id', '{{ session('nuevo_cliente_id') }}');
    sessionStorage.setItem('nuevo_cliente_nombre', '{{ session('nuevo_cliente_nombre') }}');
    sessionStorage.setItem('nuevo_cliente_telefono', '{{ session('nuevo_cliente_telefono') ?? '' }}');
</script>
@endif
@endsection
