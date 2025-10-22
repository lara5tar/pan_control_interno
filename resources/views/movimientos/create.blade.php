@extends('layouts.app')

@section('title', 'Registrar Movimiento')

@section('page-title', 'Registrar Movimiento de Inventario')
@section('page-description', 'Registra una entrada o salida de libros')

@section('content')
<x-page-layout 
    title="Registrar Movimiento"
    description="Registra una entrada o salida de libros del inventario"
    button-text="Volver a Movimientos"
    button-icon="fas fa-arrow-left"
    :button-route="route('movimientos.index')"
    :centered="true"
>
    <x-card>
        <form action="{{ route('movimientos.store') }}" method="POST" id="movimientoForm">
            @csrf

            <div class="space-y-6">
                <!-- Selección de Libro con Buscador -->
                <x-libro-search-filter 
                    name="libro_id"
                    :libros="$libros"
                    :selected="old('libro_id')"
                    label="Libro"
                    :required="true"
                />
                
                @error('libro_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror

                <!-- Tipo de Movimiento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Movimiento <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="tipo_movimiento" value="entrada" 
                                   class="hidden peer" 
                                   {{ old('tipo_movimiento') == 'entrada' ? 'checked' : '' }}
                                   required>
                            <div class="p-4 border-2 border-gray-300 rounded-lg text-center peer-checked:border-green-500 peer-checked:bg-green-50 transition-all hover:border-green-300">
                                <i class="fas fa-arrow-down text-3xl text-green-600 mb-2"></i>
                                <p class="font-medium text-gray-900">Entrada</p>
                                <p class="text-xs text-gray-500">Agregar al inventario</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="tipo_movimiento" value="salida" 
                                   class="hidden peer"
                                   {{ old('tipo_movimiento') == 'salida' ? 'checked' : '' }}
                                   required>
                            <div class="p-4 border-2 border-gray-300 rounded-lg text-center peer-checked:border-red-500 peer-checked:bg-red-50 transition-all hover:border-red-300">
                                <i class="fas fa-arrow-up text-3xl text-red-600 mb-2"></i>
                                <p class="font-medium text-gray-900">Salida</p>
                                <p class="text-xs text-gray-500">Retirar del inventario</p>
                            </div>
                        </label>
                    </div>
                    @error('tipo_movimiento')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo de Entrada (se muestra cuando tipo_movimiento = entrada) -->
                <div id="tipoEntradaContainer" class="hidden">
                    <label for="tipo_entrada" class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo de Entrada <span class="text-red-500">*</span>
                    </label>
                    <select name="tipo_entrada" id="tipo_entrada" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('tipo_entrada') border-red-500 @enderror">
                        <option value="">Selecciona el motivo</option>
                        @foreach(\App\Models\Movimiento::tiposEntrada() as $key => $label)
                            <option value="{{ $key }}" {{ old('tipo_entrada') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_entrada')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo de Salida (se muestra cuando tipo_movimiento = salida) -->
                <div id="tipoSalidaContainer" class="hidden">
                    <label for="tipo_salida" class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo de Salida <span class="text-red-500">*</span>
                    </label>
                    <select name="tipo_salida" id="tipo_salida" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('tipo_salida') border-red-500 @enderror">
                        <option value="">Selecciona el motivo</option>
                        @foreach(\App\Models\Movimiento::tiposSalida() as $key => $label)
                            <option value="{{ $key }}" {{ old('tipo_salida') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_salida')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cantidad -->
                <x-form-number
                    name="cantidad"
                    label="Cantidad"
                    :value="old('cantidad', 1)"
                    :required="true"
                    :min="1"
                    icon="fas fa-hashtag"
                    placeholder="Ej: 10"
                    helpText="Número de ejemplares"
                />

                <!-- Observaciones -->
                <div>
                    <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
                        Observaciones
                    </label>
                    <textarea 
                        name="observaciones" 
                        id="observaciones" 
                        rows="3"
                        maxlength="500"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('observaciones') border-red-500 @enderror"
                        placeholder="Notas adicionales sobre este movimiento...">{{ old('observaciones') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">
                        <i class="fas fa-info-circle"></i> Máximo 500 caracteres
                    </p>
                    @error('observaciones')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botones de Acción -->
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <x-button type="submit" variant="primary" icon="fas fa-save">
                        Registrar Movimiento
                    </x-button>
                    <x-button type="button" variant="secondary" icon="fas fa-times" onclick="window.location='{{ route('movimientos.index') }}'">
                        Cancelar
                    </x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoMovimiento = document.querySelectorAll('input[name="tipo_movimiento"]');
    const tipoEntradaContainer = document.getElementById('tipoEntradaContainer');
    const tipoSalidaContainer = document.getElementById('tipoSalidaContainer');

    // Mostrar/ocultar tipos según el movimiento
    tipoMovimiento.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'entrada') {
                tipoEntradaContainer.classList.remove('hidden');
                tipoSalidaContainer.classList.add('hidden');
                document.getElementById('tipo_entrada').required = true;
                document.getElementById('tipo_salida').required = false;
            } else {
                tipoSalidaContainer.classList.remove('hidden');
                tipoEntradaContainer.classList.add('hidden');
                document.getElementById('tipo_salida').required = true;
                document.getElementById('tipo_entrada').required = false;
            }
        });
    });

    // Escuchar cuando se selecciona un libro desde el componente
    document.getElementById('libro_id').addEventListener('libroSelected', function(e) {
        const libro = e.detail;
        // El componente ya maneja la actualización del precio y stock
    });

    // Inicializar el estado según old values
    const checkedRadio = document.querySelector('input[name="tipo_movimiento"]:checked');
    if (checkedRadio) {
        checkedRadio.dispatchEvent(new Event('change'));
    }

    // Validación de stock al enviar
    document.getElementById('movimientoForm').addEventListener('submit', function(e) {
        const tipo = document.querySelector('input[name="tipo_movimiento"]:checked');
        const libroInput = document.getElementById('libro_id');
        
        if (tipo && tipo.value === 'salida' && libroInput.value) {
            // Obtener el stock del libro seleccionado desde el stock_actual span
            const stockSpan = document.getElementById('libro_id_stock_actual');
            if (stockSpan) {
                const stock = parseInt(stockSpan.textContent);
                const cantidad = parseInt(document.getElementById('cantidad').value);
                
                if (cantidad > stock) {
                    e.preventDefault();
                    showNotification('No hay suficiente stock. Stock actual: ' + stock, 'error');
                }
            }
        }
    });
});
</script>
</x-page-layout>
@endsection
