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

<form action="{{ $action }}" method="POST" id="ventaForm" data-libro-index="{{ $venta ? $venta->movimientos->count() : 0 }}" data-cliente-selected="{{ $venta && $venta->cliente ? json_encode(['nombre' => $venta->cliente->nombre, 'telefono' => $venta->cliente->telefono]) : '' }}">
    @csrf
    @if($method !== 'POST')
        @method($method)
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
                @if($venta && $venta->movimientos->count() > 0)
                    @foreach($venta->movimientos as $index => $movimiento)
                        <x-libro-item 
                            :libros="$libros" 
                            :index="$index" 
                            :movimiento="$movimiento" />
                    @endforeach
                @endif
            </div>

            <!-- Mensaje cuando no hay libros -->
            @if(!$venta || $venta->movimientos->count() === 0)
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
</script>
<script src="{{ asset('js/cliente-search-dynamic.js') }}"></script>
<script src="{{ asset('js/libro-search-dynamic.js') }}"></script>
<script src="{{ asset('js/venta-form.js') }}"></script>
@endpush
