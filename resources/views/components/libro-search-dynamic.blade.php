{{--
    Componente: Libro Search Dynamic
    Descripción: Búsqueda dinámica de libros que funciona con arrays (para múltiples libros en ventas)
    Props:
        - name: Nombre del campo (ej: "libros[0][libro_id]")
        - index: Índice único para este campo
        - libros: Colección de libros
        - selected: ID del libro seleccionado (opcional)
        - label: Etiqueta del campo
        - required: Si el campo es requerido
--}}

@props([
    'name' => 'libro_id',
    'index' => 0,
    'selected' => null,
    'libros' => [],
    'label' => 'Libro',
    'required' => false
])

@php
    $uniqueId = 'libro_search_' . str_replace(['[', ']'], ['_', ''], $name);
@endphp

<div class="libro-search-container" id="{{ $uniqueId }}_container">
    @if($label)
        <label for="{{ $uniqueId }}_search" class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-book text-gray-400"></i> {{ $label }}
            @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif
    
    <!-- Input de búsqueda visible -->
    <div class="relative">
        <div class="relative">
            <span class="absolute left-3 top-3 text-gray-400">
                <i class="fas fa-search"></i>
            </span>
            <input 
                type="text" 
                id="{{ $uniqueId }}_search"
                class="libro-search-input w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Buscar libro..."
                autocomplete="off"
            >
            <button 
                type="button"
                class="libro-clear-btn absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Dropdown de resultados -->
        <div class="libro-dropdown absolute z-[9999] w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-64 overflow-y-auto hidden">
            <div class="libro-results divide-y divide-gray-100">
                <!-- Los resultados se insertarán aquí -->
            </div>
            <div class="libro-no-results hidden p-4 text-center text-gray-500">
                <i class="fas fa-search text-2xl mb-2 text-gray-300"></i>
                <p class="text-sm">No se encontraron libros</p>
            </div>
            <div class="libro-all-option p-3 border-t border-gray-200 bg-gray-50 hover:bg-gray-100 cursor-pointer transition-colors">
                <p class="text-sm font-medium text-gray-700">
                    <i class="fas fa-list"></i> Ver todos los libros
                </p>
            </div>
        </div>
    </div>
    
    <!-- Input hidden con el valor real -->
    <input 
        type="hidden" 
        name="{{ $name }}" 
        class="libro-id-input"
        value="{{ old($name, $selected) }}"
        data-precio=""
        data-stock=""
        @if($required) required @endif
    >
    
    <!-- Libro seleccionado -->
    <div class="libro-selected mt-2 hidden">
        <div class="p-2 bg-primary-50 border border-primary-200 rounded-lg flex items-center justify-between text-sm">
            <div class="flex-1">
                <p class="font-medium text-gray-900 libro-selected-nombre"></p>
                <p class="text-xs text-gray-600">
                    <span class="libro-selected-codigo"></span>
                    <span class="mx-2">•</span>
                    Precio: <span class="libro-selected-precio font-semibold"></span>
                    <span class="mx-2">•</span>
                    Stock: <span class="libro-selected-stock font-semibold"></span>
                </p>
            </div>
            <button 
                type="button" 
                class="libro-remove-btn ml-2 text-red-600 hover:text-red-800 transition-colors"
                title="Quitar libro"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>


