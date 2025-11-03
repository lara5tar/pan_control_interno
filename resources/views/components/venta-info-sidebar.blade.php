{{--
    Componente: Venta Info Sidebar
    Descripción: Panel lateral con información básica de la venta
    Props:
        - venta: Objeto venta existente (opcional)
        - codigo: Código de venta generado
--}}

@props([
    'venta' => null,
    'codigo' => null
])

<div class="bg-gradient-to-br from-gray-50 to-white rounded-lg border border-gray-200 p-6 sticky top-6">
    <!-- Header -->
    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
        <i class="fas fa-info-circle text-primary-600 mr-2"></i>
        Información de la Venta
    </h3>

    <!-- Código de Venta -->
    <div class="mb-4">
        <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
            Código de Venta <span class="text-red-500">*</span>
        </label>
        <input 
            type="text" 
            name="codigo" 
            id="codigo" 
            value="{{ old('codigo', $venta?->codigo ?? $codigo) }}"
            readonly
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg font-mono font-bold text-lg text-gray-600 cursor-not-allowed">
    </div>

    <!-- Cliente Selector -->
    <x-cliente-selector :venta="$venta" />

    <!-- Fecha de Venta -->
    <div class="mb-4">
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
                value="{{ old('fecha_venta', $venta?->fecha_venta?->format('Y-m-d') ?? now()->toDateString()) }}"
                max="{{ now()->toDateString() }}"
                required
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('fecha_venta') border-red-500 @enderror">
        </div>
        @error('fecha_venta')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Tipo de Pago -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Tipo de Pago <span class="text-red-500">*</span>
        </label>
        <div class="space-y-2">
            @foreach([
                'contado' => ['icon' => 'fa-money-bill-wave', 'color' => 'green', 'label' => 'Contado'],
                'credito' => ['icon' => 'fa-credit-card', 'color' => 'blue', 'label' => 'Crédito'],
                'mixto' => ['icon' => 'fa-exchange-alt', 'color' => 'purple', 'label' => 'Mixto']
            ] as $tipo => $data)
                <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                    <input 
                        type="radio" 
                        name="tipo_pago" 
                        value="{{ $tipo }}" 
                        {{ old('tipo_pago', $venta?->tipo_pago ?? ($tipo === 'contado' ? 'checked' : '')) === $tipo ? 'checked' : '' }}
                        class="text-primary-600 focus:ring-primary-500">
                    <span class="ml-3">
                        <i class="fas {{ $data['icon'] }} text-{{ $data['color'] }}-600"></i>
                        <strong>{{ $data['label'] }}</strong>
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    <!-- Descuento Global -->
    <div class="mb-4">
        <label for="descuento_global" class="block text-sm font-medium text-gray-700 mb-2">
            Descuento Global (%)
        </label>
        <div class="relative">
            <span class="absolute left-3 top-2.5 text-gray-400">
                <i class="fas fa-percentage"></i>
            </span>
            <input 
                type="number" 
                name="descuento_global" 
                id="descuento_global" 
                min="0"
                max="100"
                step="0.01"
                value="{{ old('descuento_global', $venta?->descuento_global ?? 0) }}"
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
        </div>
        <p class="mt-1 text-xs text-gray-500">
            <i class="fas fa-info-circle"></i> Descuento adicional sobre el total
        </p>
    </div>

    <!-- Observaciones -->
    <div class="mb-6">
        <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
            Observaciones
        </label>
        <textarea 
            name="observaciones" 
            id="observaciones" 
            rows="3"
            maxlength="500"
            placeholder="Notas adicionales..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('observaciones', $venta?->observaciones) }}</textarea>
    </div>

    <!-- Resumen de Totales -->
    <div class="border-t border-gray-200 pt-4 bg-gradient-to-br from-primary-50 to-white rounded-lg p-4">
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal:</span>
                <span id="subtotalDisplay" class="font-semibold">$0.00</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Descuento Global:</span>
                <span id="descuentoDisplay" class="font-semibold text-orange-600">-$0.00</span>
            </div>
            <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                <span class="text-gray-800">Total:</span>
                <span id="totalDisplay" class="text-primary-600">$0.00</span>
            </div>
        </div>
    </div>
</div>
