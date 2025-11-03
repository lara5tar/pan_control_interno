{{--
    Componente: Cliente Search Dynamic
    Descripción: Búsqueda dinámica de clientes (IDÉNTICO al componente de libros)
    Props:
        - name: Nombre del campo (default: "cliente_id")
        - selected: ID del cliente seleccionado (opcional)
        - label: Etiqueta del campo
        - required: Si el campo es requerido
--}}

@props([
    'name' => 'cliente_id',
    'selected' => null,
    'label' => 'Cliente',
    'required' => false
])

@php
    $uniqueId = 'cliente_search_' . str_replace(['[', ']'], ['_', ''], $name);
@endphp

<div class="cliente-search-container" id="{{ $uniqueId }}_container">
    @if($label)
        <label for="{{ $uniqueId }}_search" class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-user text-gray-400"></i> {{ $label }}
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
                class="cliente-search-input w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Buscar cliente..."
                autocomplete="off"
            >
            <button 
                type="button"
                class="cliente-clear-btn absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Dropdown de resultados -->
        <div class="cliente-dropdown absolute z-[9999] w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-64 overflow-y-auto hidden">
            <div class="cliente-results divide-y divide-gray-100">
                <!-- Los resultados se insertarán aquí -->
            </div>
            <div class="cliente-no-results hidden p-4 text-center text-gray-500">
                <i class="fas fa-search text-2xl mb-2 text-gray-300"></i>
                <p class="text-sm">No se encontraron clientes</p>
            </div>
            <div class="cliente-all-option p-3 border-t border-gray-200 bg-gray-50 hover:bg-gray-100 cursor-pointer transition-colors">
                <p class="text-sm font-medium text-gray-700">
                    <i class="fas fa-plus-circle"></i> Crear nuevo cliente
                </p>
            </div>
        </div>
    </div>
    
    <!-- Input hidden con el valor real -->
    <input 
        type="hidden" 
        name="{{ $name }}" 
        class="cliente-id-input"
        value="{{ old($name, $selected) }}"
        data-nombre=""
        data-telefono=""
        @if($required) required @endif
    >
    
    <!-- Cliente seleccionado -->
    <div class="cliente-selected mt-2 hidden">
        <div class="p-2 bg-primary-50 border border-primary-200 rounded-lg flex items-center justify-between text-sm">
            <div class="flex-1">
                <p class="font-medium text-gray-900 cliente-selected-nombre"></p>
                <p class="text-xs text-gray-600">
                    <span class="cliente-selected-codigo"></span>
                    <span class="mx-2">•</span>
                    Teléfono: <span class="cliente-selected-telefono font-semibold"></span>
                </p>
            </div>
            <button 
                type="button" 
                class="cliente-remove-btn ml-2 text-red-600 hover:text-red-800 transition-colors"
                title="Quitar cliente"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>




