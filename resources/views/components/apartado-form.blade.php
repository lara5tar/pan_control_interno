{{--
    Componente: Apartado Form
    Props: apartado, action, method, submitText, libros, clientes
--}}

@props([
    'apartado' => null,
    'action',
    'method' => 'POST',
    'submitText' => 'Guardar',
    'libros' => [],
    'clientes' => []
])

@php
    $oldLibros = old('libros', []);
    $libroCount = !empty($oldLibros) ? count($oldLibros) : ($apartado ? $apartado->detalles->count() : 0);
@endphp

<form action="{{ $action }}" method="POST" id="apartadoForm" data-libro-index="{{ $libroCount }}">
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
            <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-2">
                Cliente <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <i class="fas fa-user"></i>
                </span>
                <select 
                    name="cliente_id" 
                    id="cliente_id" 
                    required
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('cliente_id') border-red-500 @enderror">
                    <option value="">Selecciona un cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ old('cliente_id', $apartado?->cliente_id) == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('cliente_id')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Fecha de Apartado -->
        <div>
            <label for="fecha_apartado" class="block text-sm font-medium text-gray-700 mb-2">
                Fecha de Apartado <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <i class="fas fa-calendar"></i>
                </span>
                <input 
                    type="date" 
                    name="fecha_apartado" 
                    id="fecha_apartado" 
                    value="{{ old('fecha_apartado', $apartado?->fecha_apartado?->format('Y-m-d') ?? date('Y-m-d')) }}"
                    required
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('fecha_apartado') border-red-500 @enderror">
            </div>
            @error('fecha_apartado')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Fecha Límite -->
        <div>
            <label for="fecha_limite" class="block text-sm font-medium text-gray-700 mb-2">
                Fecha Límite
            </label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <i class="fas fa-calendar-check"></i>
                </span>
                <input 
                    type="date" 
                    name="fecha_limite" 
                    id="fecha_limite" 
                    value="{{ old('fecha_limite', $apartado?->fecha_limite?->format('Y-m-d')) }}"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('fecha_limite') border-red-500 @enderror">
            </div>
            <p class="mt-1 text-sm text-gray-500">
                <i class="fas fa-info-circle"></i> Fecha sugerida para liquidar el apartado (opcional)
            </p>
            @error('fecha_limite')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Enganche -->
        <div>
            <label for="enganche" class="block text-sm font-medium text-gray-700 mb-2">
                Enganche / Anticipo <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <i class="fas fa-dollar-sign"></i>
                </span>
                <input 
                    type="number" 
                    name="enganche" 
                    id="enganche" 
                    step="0.01"
                    min="0"
                    value="{{ old('enganche', $apartado?->enganche ?? 0) }}"
                    required
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('enganche') border-red-500 @enderror"
                    placeholder="0.00">
            </div>
            <p class="mt-1 text-sm text-gray-500">
                <i class="fas fa-info-circle"></i> Monto inicial que el cliente pagará
            </p>
            @error('enganche')
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
                    min="0" 
                    max="100" 
                    step="0.01"
                    value="{{ old('descuento_global', 0) }}"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('descuento_global') border-red-500 @enderror"
                    placeholder="0.00">
            </div>
            <p class="mt-1 text-sm text-gray-500">
                <i class="fas fa-info-circle"></i> Se aplicará a todos los libros
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
                placeholder="Notas adicionales sobre este apartado...">{{ old('observaciones', $apartado?->observaciones) }}</textarea>
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
                    Libros a Apartar
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
                    $hasApartadoDetalles = $apartado && $apartado->detalles->count() > 0;
                @endphp

                @if($hasOldLibros)
                    {{-- Renderizar libros desde old() cuando hay error de validación --}}
                    @foreach($oldLibros as $index => $oldLibro)
                        <x-libro-item 
                            :libros="$libros" 
                            :index="$index"
                            :oldData="$oldLibro" />
                    @endforeach
                @elseif($hasApartadoDetalles)
                    {{-- Renderizar libros del apartado existente --}}
                    @foreach($apartado->detalles as $index => $detalle)
                        <x-libro-item 
                            :libros="$libros" 
                            :index="$index" 
                            :oldData="[
                                'libro_id' => $detalle->libro_id,
                                'cantidad' => $detalle->cantidad,
                                'descuento' => $detalle->descuento ?? 0
                            ]" />
                    @endforeach
                @endif
            </div>

            <!-- Mensaje cuando no hay libros -->
            @if(!$hasOldLibros && (!$apartado || $apartado->detalles->count() === 0))
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
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Total:</span>
                    <span class="font-semibold text-gray-900" id="totalDisplay">$0.00</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Enganche:</span>
                    <span class="font-semibold text-green-600" id="engancheDisplay">$0.00</span>
                </div>
                <div class="flex justify-between text-lg font-bold border-t border-gray-300 pt-2">
                    <span class="text-gray-900">Saldo Pendiente:</span>
                    <span class="text-orange-600" id="saldoDisplay">$0.00</span>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="lg:col-span-2 flex justify-end gap-3 pt-4 border-t border-gray-200">
            <x-button 
                type="button" 
                variant="secondary" 
                icon="fas fa-times" 
                onclick="window.location='{{ route('apartados.index') }}'">
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
    window.apartadoLibrosData = @json($libros);
    
    // Prevenir que el botón quede deshabilitado al mostrar alert de validación
    document.addEventListener('DOMContentLoaded', function() {
        const apartadoForm = document.getElementById('apartadoForm');
        if (apartadoForm) {
            apartadoForm.addEventListener('submit', function(e) {
                const submitButton = apartadoForm.querySelector('button[type="submit"]');
                if (submitButton) {
                    // Si la validación falla (el alert se muestra), reactivar el botón después
                    setTimeout(() => {
                        submitButton.disabled = false;
                        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    }, 100);
                }
            });
        }
    });
</script>
<script src="{{ asset('js/libro-search-dynamic.js') }}"></script>
<script src="{{ asset('js/apartado-form.js') }}"></script>
@endpush
