{{--
    Componente: Libro Item Template
    Descripción: Template para un libro individual en el carrito
    Props:
        - libros: Colección de libros disponibles
        - index: Índice del libro (opcional, para edición)
        - movimiento: Movimiento existente (opcional, para edición)
        - oldData: Datos antiguos del formulario (cuando hay error de validación)
--}}

@props([
    'libros' => [],
    'index' => null,
    'movimiento' => null,
    'oldData' => null
])

@php
    $isTemplate = is_null($index);
    $indexValue = $isTemplate ? 'INDEX_PLACEHOLDER' : $index;
    $numeroLibro = $isTemplate ? 1 : ($index + 1);
    
    // Determinar qué datos usar (oldData tiene prioridad sobre movimiento)
    $libroId = $oldData['libro_id'] ?? $movimiento?->libro_id ?? null;
    $cantidad = $oldData['cantidad'] ?? $movimiento?->cantidad ?? 1;
    $descuento = $oldData['descuento'] ?? $movimiento?->descuento ?? 0;
    
    // Detectar libro eliminado
    $libroEliminado = false; // Default value
    if ($movimiento && $movimiento->libro_id && !$movimiento->libro) {
        $libroEliminado = true;
    }
    
    $precioUnitario = $libroEliminado ? $movimiento->precio_unitario : 0;
@endphp

@if($libroEliminado)
{{-- LIBRO ELIMINADO - Permitir cambiar por otro libro --}}
<div class="libro-item border border-orange-300 rounded-lg p-4 bg-orange-50" data-index="{{ $indexValue }}" style="border-color: #fed7aa !important;">
    <!-- Header -->
    <div class="flex justify-between items-start mb-3">
        <h4 class="font-semibold text-orange-800">
            Libro #<span class="libro-number">{{ $numeroLibro }}</span>
        </h4>
        <button type="button" class="remove-libro text-red-500 hover:text-red-700 transition-colors text-xl" title="Eliminar de la venta">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Alerta de Libro Eliminado -->
        <div class="md:col-span-2">
            <div class="bg-orange-100 border border-orange-300 rounded-lg p-3">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-orange-600 mr-2 mt-1"></i>
                    <div class="text-sm text-orange-800">
                        <strong class="font-semibold">Este libro ya no existe en el inventario (ID: {{ $libroId }}).</strong>
                        <p class="mt-1">Puedes cambiar este libro por otro disponible o eliminarlo de la venta.</p>
                        <p class="mt-1 text-xs text-gray-600">
                            <strong>Cantidad original:</strong> {{ $cantidad }} | 
                            <strong>Descuento original:</strong> {{ $descuento }}% | 
                            <strong>Precio unitario:</strong> ${{ number_format($precioUnitario, 2) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selector de Libro con Buscador -->
        <div class="md:col-span-2">
            <x-libro-search-dynamic 
                :name="'libros[' . $indexValue . '][libro_id]'"
                :index="$indexValue"
                :libros="$libros"
                :selected="null"
                label="Seleccionar Nuevo Libro (Requerido)"
                :required="true"
            />
            <p class="mt-1 text-xs text-orange-600">
                <i class="fas fa-info-circle"></i> Debes seleccionar un libro de reemplazo
            </p>
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
                value="{{ $cantidad }}"
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
                value="{{ $descuento }}">
        </div>
        
        <!-- Precio Unitario (Hidden) -->
        <input 
            type="hidden" 
            name="libros[{{ $indexValue }}][precio_unitario]" 
            class="precio-unitario-input" 
            value="{{ $libroEliminado ? $precioUnitario : 0 }}">
    </div>

    <!-- Subtotal -->
    <div class="mt-3 pt-3 border-t border-orange-200 flex justify-between items-center">
        <span class="text-sm text-gray-600">Subtotal:</span>
        <span class="subtotal-libro font-bold text-orange-600">$0.00</span>
    </div>
    
    <!-- Botón Agregar Otro Libro (solo se mostrará en el último libro) -->
    <div class="add-libro-inline-container hidden mt-3">
        <button 
            type="button" 
            class="add-libro-inline w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2"
            onclick="if(window.ventaFormManagerInstance) { window.ventaFormManagerInstance.addLibro(); } else if(window.apartadoFormManagerInstance) { window.apartadoFormManagerInstance.addLibro(); }"
        >
            <i class="fas fa-plus"></i>
            <span>Agregar Otro Libro</span>
        </button>
    </div>
</div>
@else
{{-- LIBRO NORMAL - Editable --}}
<div class="libro-item border border-gray-200 rounded-lg p-4 bg-gray-50" data-index="{{ $indexValue }}" style="border-color: #e5e7eb !important;">
    <!-- Header -->
    <div class="flex justify-between items-start mb-3">
        <h4 class="font-semibold text-gray-800">
            Libro #<span class="libro-number">{{ $numeroLibro }}</span>
        </h4>
        <button type="button" class="remove-libro text-red-500 hover:text-red-700 transition-colors text-xl" title="Eliminar de la venta">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Selector de Libro con Buscador -->
        <div class="md:col-span-2">
            <x-libro-search-dynamic 
                :name="'libros[' . $indexValue . '][libro_id]'"
                :index="$indexValue"
                :libros="$libros"
                :selected="$libroId"
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
                value="{{ $cantidad }}"
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
                value="{{ $descuento }}">
        </div>

        <!-- Precio Unitario Editable (Solo para Admins) -->
        @isAdmin
        <div class="md:col-span-2">
            <div class="flex items-center gap-3 text-sm text-gray-600">
                <input 
                    type="checkbox" 
                    class="precio-custom-checkbox h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
                    id="precio_custom_checkbox_{{ $indexValue }}"
                    onchange="togglePrecioCustom({{ $indexValue }})">
                <label for="precio_custom_checkbox_{{ $indexValue }}" class="cursor-pointer">
                    Precio personalizado
                </label>
                <div class="precio-custom-input-container hidden flex items-center gap-2">
                    <span class="text-gray-500">$</span>
                    <input 
                        type="number" 
                        name="libros[{{ $indexValue }}][precio_custom]" 
                        class="precio-custom-input w-24 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-400 focus:border-gray-400" 
                        min="0" 
                        step="0.01"
                        placeholder="0.00"
                        disabled>
                </div>
            </div>
        </div>
        @endisAdmin
        
        <!-- Precio Unitario (Hidden) -->
        <input 
            type="hidden" 
            name="libros[{{ $indexValue }}][precio_unitario]" 
            class="precio-unitario-input" 
            value="0">
    </div>

    <!-- Subtotal -->
    <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between items-center">
        <span class="text-sm text-gray-600">Subtotal:</span>
        <span class="subtotal-libro font-bold text-primary-600">$0.00</span>
    </div>
    
    <!-- Botón Agregar Otro Libro (solo se mostrará en el último libro) -->
    <div class="add-libro-inline-container hidden mt-3">
        <button 
            type="button" 
            class="add-libro-inline w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2"
            onclick="if(window.ventaFormManagerInstance) { window.ventaFormManagerInstance.addLibro(); } else if(window.apartadoFormManagerInstance) { window.apartadoFormManagerInstance.addLibro(); }"
        >
            <i class="fas fa-plus"></i>
            <span>Agregar Otro Libro</span>
        </button>
    </div>
</div>
@endif

<script>
/**
 * Toggle del campo de precio personalizado (solo para admin)
 */
function togglePrecioCustom(index) {
    const checkbox = document.getElementById(`precio_custom_checkbox_${index}`);
    const libroItem = checkbox.closest('.libro-item');
    const container = libroItem.querySelector('.precio-custom-input-container');
    const input = libroItem.querySelector('.precio-custom-input');
    
    if (checkbox.checked) {
        // Mostrar el campo de precio
        container.classList.remove('hidden');
        input.disabled = false;
        input.required = true;
        
        // Obtener el precio actual del libro para pre-llenar
        const libroSelect = libroItem.querySelector('input[name*="[libro_id]"]');
        if (libroSelect && libroSelect.value) {
            const precioActual = parseFloat(libroSelect.getAttribute('data-precio')) || 0;
            input.value = precioActual.toFixed(2);
        }
    } else {
        // Ocultar el campo de precio
        container.classList.add('hidden');
        input.disabled = true;
        input.required = false;
        input.value = '';
    }
    
    // Recalcular totales
    if (window.ventaFormManagerInstance) {
        window.ventaFormManagerInstance.calculateTotal();
    }
}
</script>
