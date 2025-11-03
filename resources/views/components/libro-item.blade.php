{{--
    Componente: Libro Item Template
    Descripción: Template para un libro individual en el carrito
    Props:
        - libros: Colección de libros disponibles
        - index: Índice del libro (opcional, para edición)
        - movimiento: Movimiento existente (opcional, para edición)
--}}

@props([
    'libros' => [],
    'index' => null,
    'movimiento' => null
])

@php
    $isTemplate = is_null($index);
    $indexValue = $isTemplate ? 'INDEX_PLACEHOLDER' : $index;
    $numeroLibro = $isTemplate ? 1 : ($index + 1);
@endphp

<div class="libro-item border border-gray-200 rounded-lg p-4 bg-gray-50" data-index="{{ $indexValue }}">
    <!-- Header -->
    <div class="flex justify-between items-start mb-3">
        <h4 class="font-semibold text-gray-800">
            Libro #<span class="libro-number">{{ $numeroLibro }}</span>
        </h4>
        <button type="button" class="remove-libro text-red-600 hover:text-red-700 transition-colors">
            <i class="fas fa-times-circle"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Selector de Libro con Buscador -->
        <div class="md:col-span-2">
            <x-libro-search-dynamic 
                :name="'libros[' . $indexValue . '][libro_id]'"
                :index="$indexValue"
                :libros="$libros"
                :selected="$movimiento?->libro_id ?? null"
                label="Seleccionar Libro"
                :required="true"
            />
        </div>

        <!-- Cantidad -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Cantidad <span class="text-red-500">*</span>
            </label>
            <input 
                type="number" 
                name="libros[{{ $indexValue }}][cantidad]" 
                class="cantidad-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500" 
                min="1" 
                value="{{ $movimiento?->cantidad ?? 1 }}"
                required>
            <p class="stock-message text-xs text-gray-500 mt-1"></p>
        </div>

        <!-- Descuento -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Descuento (%)
            </label>
            <input 
                type="number" 
                name="libros[{{ $indexValue }}][descuento]" 
                class="descuento-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500" 
                min="0" 
                max="100" 
                step="0.01"
                value="{{ $movimiento?->descuento ?? 0 }}">
        </div>
    </div>

    <!-- Subtotal -->
    <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between items-center">
        <span class="text-sm text-gray-600">Subtotal:</span>
        <span class="subtotal-libro font-bold text-primary-600">$0.00</span>
    </div>
</div>
