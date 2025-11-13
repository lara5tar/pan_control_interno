{{--
    Componente: Venta Form
    Props: venta, action, method, submitText, codigo, libros
--}}

@props([
    'venta' => null,
    'action',
    'method' => 'POST',
    'submitText' => 'Guardar',
    'libros' => []
])

@php
    $oldLibros = old('libros', []);
    $libroCount = !empty($oldLibros) ? count($oldLibros) : ($venta ? $venta->movimientos->count() : 0);
@endphp

<form action="{{ $action }}" method="POST" id="ventaForm" data-libro-index="{{ $libroCount }}" data-cliente-selected="{{ $venta && $venta->cliente ? json_encode(['nombre' => $venta->cliente->nombre, 'telefono' => $venta->cliente->telefono]) : '' }}">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    {{-- Mensajes de error generales --}}
    @if($errors->has('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-start">
                <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                <div>
                    <p class="text-sm font-medium text-red-800">{{ $errors->first('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any() && !$errors->has('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-start">
                <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                <div>
                    <p class="text-sm font-medium text-red-800 mb-2">Por favor corrige los siguientes errores:</p>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cliente -->
        <div class="lg:col-span-2">
            <x-cliente-search-dynamic 
                name="cliente_id"
                :selected="$venta?->cliente_id ?? null"
                label="Cliente (Opcional)"
                :required="false"
            />
        </div>

        <!-- Fecha de Venta -->
        <div>
            <label for="fecha_venta" class="block text-sm font-medium text-gray-700 mb-2">
                Fecha de Venta <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <i class="fas fa-calendar"></i>
                </span>
                <input 
                    type="date" 
                    name="fecha_venta" 
                    id="fecha_venta" 
                    value="{{ old('fecha_venta', $venta?->fecha_venta ?? date('Y-m-d')) }}"
                    required
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('fecha_venta') border-red-500 @enderror">
            </div>
            @error('fecha_venta')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tipo de Pago -->
        <div>
            <label for="tipo_pago" class="block text-sm font-medium text-gray-700 mb-2">
                Tipo de Pago <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <i class="fas fa-credit-card"></i>
                </span>
                <select 
                    name="tipo_pago" 
                    id="tipo_pago" 
                    required
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('tipo_pago') border-red-500 @enderror">
                    <option value="">Selecciona el tipo de pago</option>
                    <option value="contado" {{ old('tipo_pago', $venta?->tipo_pago ?? 'contado') == 'contado' ? 'selected' : '' }}>Contado</option>
                    <option value="credito" {{ old('tipo_pago', $venta?->tipo_pago) == 'credito' ? 'selected' : '' }}>Crédito</option>
                    <option value="mixto" {{ old('tipo_pago', $venta?->tipo_pago) == 'mixto' ? 'selected' : '' }}>Mixto</option>
                </select>
            </div>
            <p class="mt-1 text-sm text-gray-500">
                <i class="fas fa-info-circle"></i> Forma en que se realizará el pago
            </p>
            @error('tipo_pago')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Descuento Global -->
        <div>
            <label for="descuento_global" class="block text-sm font-medium text-gray-700 mb-2">
                Descuento Global (%)
            </label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <i class="fas fa-percent"></i>
                </span>
                <input 
                    type="number" 
                    name="descuento_global" 
                    id="descuento_global" 
                    step="0.01"
                    min="0"
                    max="100"
                    value="{{ old('descuento_global', $venta?->descuento_global ?? 0) }}"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('descuento_global') border-red-500 @enderror"
                    placeholder="0">
            </div>
            <p class="mt-1 text-sm text-gray-500">
                <i class="fas fa-info-circle"></i> Se aplicará sobre el total de la venta
            </p>
            @error('descuento_global')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Venta a Plazos -->
        <div class="lg:col-span-2">
            <div class="flex items-start space-x-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <input 
                    type="checkbox" 
                    name="es_a_plazos" 
                    id="es_a_plazos" 
                    value="1"
                    {{ old('es_a_plazos', $venta?->es_a_plazos) ? 'checked' : '' }}
                    class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                <div class="flex-1">
                    <label for="es_a_plazos" class="block text-sm font-medium text-gray-900 cursor-pointer">
                        <i class="fas fa-calendar-alt text-blue-600"></i> Venta a Plazos
                    </label>
                    <p class="text-sm text-gray-600 mt-1">
                        <i class="fas fa-info-circle"></i> 
                        El stock NO se descontará hasta que la venta esté completamente pagada. 
                        El cliente debe estar registrado para ventas a plazos.
                    </p>
                </div>
            </div>
        </div>

        <!-- Fecha Límite (solo visible si es a plazos) -->
        <div id="fechaLimiteContainer" class="lg:col-span-2 hidden">
            <label for="fecha_limite" class="block text-sm font-medium text-gray-700 mb-2">
                Fecha Límite de Pago
            </label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <i class="fas fa-calendar-check"></i>
                </span>
                <input 
                    type="date" 
                    name="fecha_limite" 
                    id="fecha_limite" 
                    value="{{ old('fecha_limite', $venta?->fecha_limite?->format('Y-m-d')) }}"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('fecha_limite') border-red-500 @enderror">
            </div>
            <p class="mt-1 text-sm text-gray-500">
                <i class="fas fa-info-circle"></i> Fecha sugerida para completar el pago (opcional)
            </p>
            @error('fecha_limite')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Observaciones -->
        <div class="lg:col-span-2">
            <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
                Observaciones
            </label>
            <textarea 
                name="observaciones" 
                id="observaciones" 
                rows="3"
                maxlength="500"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('observaciones') border-red-500 @enderror"
                placeholder="Notas adicionales sobre esta venta...">{{ old('observaciones', $venta?->observaciones) }}</textarea>
            <p class="mt-1 text-sm text-gray-500">
                <i class="fas fa-info-circle"></i> Máximo 500 caracteres
            </p>
            @error('observaciones')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Sección de Libros -->
        <div class="lg:col-span-2 border-t border-gray-200 pt-6 mt-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-books text-primary-600 mr-2"></i>
                    Libros de la Venta
                </h3>
                <x-button 
                    type="button" 
                    id="addLibroBtn"
                    variant="success" 
                    size="sm"
                    icon="fas fa-plus">
                    Agregar Libro
                </x-button>
            </div>

            <!-- Contenedor de Libros -->
            <div id="librosContainer" class="space-y-4">
                @php
                    $oldLibros = old('libros', []);
                    $hasOldLibros = !empty($oldLibros);
                    $hasVentaMovimientos = $venta && $venta->movimientos->count() > 0;
                @endphp

                @if($hasOldLibros)
                    {{-- Renderizar libros desde old() cuando hay error de validación --}}
                    @foreach($oldLibros as $index => $oldLibro)
                        <x-libro-item 
                            :libros="$libros" 
                            :index="$index"
                            :oldData="$oldLibro" />
                    @endforeach
                @elseif($hasVentaMovimientos)
                    {{-- Renderizar libros de la venta existente --}}
                    @foreach($venta->movimientos as $index => $movimiento)
                        <x-libro-item 
                            :libros="$libros" 
                            :index="$index" 
                            :movimiento="$movimiento" />
                    @endforeach
                @endif
            </div>

            <!-- Mensaje cuando no hay libros -->
            @if(!$hasOldLibros && (!$venta || $venta->movimientos->count() === 0))
                <div id="emptyMessage" class="text-center py-12 text-gray-500 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                    <i class="fas fa-book text-4xl mb-3"></i>
                    <p>No hay libros agregados. Haz clic en "Agregar Libro" para empezar.</p>
                </div>
            @endif
        </div>

        <!-- Totales -->
        <div class="lg:col-span-2 bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-semibold text-gray-900" id="subtotalDisplay">$0.00</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Descuento:</span>
                    <span class="font-semibold text-red-600" id="descuentoDisplay">-$0.00</span>
                </div>
                <div class="flex justify-between text-lg font-bold border-t border-gray-300 pt-2">
                    <span class="text-gray-900">Total:</span>
                    <span class="text-primary-600" id="totalDisplay">$0.00</span>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="lg:col-span-2 flex justify-end gap-3 pt-4 border-t border-gray-200">
            <x-button 
                type="button" 
                variant="secondary" 
                icon="fas fa-times" 
                onclick="window.location='{{ route('ventas.index') }}'">
                Cancelar
            </x-button>
            <x-button 
                type="submit" 
                variant="primary" 
                icon="fas fa-save">
                {{ $submitText }}
            </x-button>
        </div>
    </div>
</form>

<!-- Template para nuevos libros -->
<template id="libroTemplate">
    <x-libro-item :libros="$libros" />
</template>

{{-- Cargar script externo --}}
@push('scripts')
<script>
    // Global libros data for libro search components
    window.ventaLibrosData = @json($libros);
    
    // Gestión de Venta a Plazos con validación de cliente
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxPlazos = document.getElementById('es_a_plazos');
        const fechaLimiteContainer = document.getElementById('fechaLimiteContainer');
        const clienteIdInput = document.querySelector('input[name="cliente_id"]');
        
        // Función para verificar si hay cliente seleccionado
        function tieneClienteSeleccionado() {
            return clienteIdInput && clienteIdInput.value && clienteIdInput.value !== '';
        }
        
        // Función para actualizar el estado del checkbox según si hay cliente
        function actualizarEstadoCheckbox() {
            const hayCliente = tieneClienteSeleccionado();
            
            if (!hayCliente) {
                // Si no hay cliente, deshabilitar y desmarcar
                checkboxPlazos.disabled = true;
                checkboxPlazos.checked = false;
                fechaLimiteContainer.classList.add('hidden');
                
                // Cambiar apariencia para indicar que está deshabilitado
                checkboxPlazos.parentElement.parentElement.style.opacity = '0.5';
                checkboxPlazos.parentElement.parentElement.style.cursor = 'not-allowed';
            } else {
                // Si hay cliente, habilitar
                checkboxPlazos.disabled = false;
                checkboxPlazos.parentElement.parentElement.style.opacity = '1';
                checkboxPlazos.parentElement.parentElement.style.cursor = 'pointer';
            }
        }
        
        // Función para mostrar/ocultar fecha límite
        function toggleFechaLimite() {
            if (checkboxPlazos.checked && tieneClienteSeleccionado()) {
                fechaLimiteContainer.classList.remove('hidden');
            } else {
                fechaLimiteContainer.classList.add('hidden');
            }
        }
        
        // Evento al intentar marcar el checkbox
        checkboxPlazos.addEventListener('click', function(e) {
            if (!tieneClienteSeleccionado()) {
                e.preventDefault();
                alert('Debe seleccionar un cliente antes de marcar como venta a plazos.');
                return false;
            }
        });
        
        // Evento al cambiar el checkbox
        checkboxPlazos.addEventListener('change', toggleFechaLimite);
        
        // Escuchar cambios en el campo de cliente
        if (clienteIdInput) {
            // Crear un observer para detectar cambios en el valor del input
            const observer = new MutationObserver(function() {
                actualizarEstadoCheckbox();
            });
            
            // Observar cambios en los atributos del input
            observer.observe(clienteIdInput, {
                attributes: true,
                attributeFilter: ['value']
            });
            
            // También escuchar el evento change
            clienteIdInput.addEventListener('change', function() {
                actualizarEstadoCheckbox();
            });
            
            // Escuchar eventos personalizados del componente de búsqueda
            document.addEventListener('clienteSeleccionado', function() {
                actualizarEstadoCheckbox();
            });
            
            document.addEventListener('clienteRemovido', function() {
                actualizarEstadoCheckbox();
            });
        }
        
        // Ejecutar al cargar
        actualizarEstadoCheckbox();
        toggleFechaLimite();
    });
</script>
<script src="{{ asset('js/cliente-search-dynamic.js') }}"></script>
<script src="{{ asset('js/libro-search-dynamic.js') }}"></script>
<script src="{{ asset('js/venta-form.js') }}"></script>
@endpush
