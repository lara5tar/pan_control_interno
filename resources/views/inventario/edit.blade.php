@extends('layouts.app')

@section('title', 'Editar Libro')

@section('page-title', 'Editar Libro')
@section('page-description', 'Actualiza la información del libro')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-card>
        <form action="{{ route('inventario.update', $libro->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Libro <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nombre" 
                        id="nombre" 
                        value="{{ old('nombre', $libro->nombre) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('nombre') border-red-500 @enderror"
                        required
                    >
                    @error('nombre')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Código de Barras -->
                <div>
                    <label for="codigo_barras" class="block text-sm font-medium text-gray-700 mb-2">
                        Código de Barras <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="codigo_barras" 
                        id="codigo_barras" 
                        value="{{ old('codigo_barras', $libro->codigo_barras) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('codigo_barras') border-red-500 @enderror"
                        required
                    >
                    @error('codigo_barras')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Precio -->
                <div>
                    <label for="precio" class="block text-sm font-medium text-gray-700 mb-2">
                        Precio <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                        <input 
                            type="number" 
                            name="precio" 
                            id="precio" 
                            value="{{ old('precio', $libro->precio) }}"
                            step="0.01"
                            min="0"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('precio') border-red-500 @enderror"
                            required
                        >
                    </div>
                    @error('precio')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Stock -->
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">
                        Stock <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="stock" 
                        id="stock" 
                        value="{{ old('stock', $libro->stock) }}"
                        min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('stock') border-red-500 @enderror"
                        required
                    >
                    @error('stock')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botones -->
                <div class="flex gap-3 pt-4">
                    <x-button type="submit" variant="primary" icon="fas fa-save">
                        Actualizar Libro
                    </x-button>
                    <x-button type="button" variant="secondary" icon="fas fa-times" onclick="window.location='{{ route('inventario.index') }}'">
                        Cancelar
                    </x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection
