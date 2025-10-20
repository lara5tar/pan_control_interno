@props([
    'libro' => null,
    'action',
    'method' => 'POST',
    'submitText' => 'Guardar'
])

<form action="{{ $action }}" method="POST">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-6">
        <!-- Nombre del Libro -->
        <x-form-input
            name="nombre"
            label="Nombre del Libro"
            :value="$libro?->nombre ?? ''"
            :required="true"
            placeholder="Ej: Cien años de soledad"
            icon="fas fa-book"
            helpText="Ingresa el nombre completo del libro"
        />

        <!-- Código de Barras -->
        <x-form-input
            name="codigo_barras"
            label="Código de Barras"
            :value="$libro?->codigo_barras ?? ''"
            :required="true"
            placeholder="Ej: 9781234567890"
            icon="fas fa-barcode"
            helpText="Código único del libro (ISBN o código de barras)"
        />

        <!-- Precio -->
        <x-form-number
            name="precio"
            label="Precio"
            :value="$libro?->precio ?? ''"
            :required="true"
            :min="0"
            step="0.01"
            prefix="$"
            placeholder="0.00"
            helpText="Precio de venta del libro"
        />

        <!-- Stock -->
        @if($libro)
            <!-- Campo de solo lectura cuando se está editando -->
            <div>
                <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">
                    Stock Disponible
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-400">
                        <i class="fas fa-boxes"></i>
                    </span>
                    <input 
                        type="text" 
                        id="stock" 
                        value="{{ $libro->stock }}"
                        readonly
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                    >
                </div>
                <p class="mt-1 text-sm text-amber-600">
                    <i class="fas fa-info-circle"></i> El stock no se puede editar directamente. Usa las funciones de entrada/salida de inventario.
                </p>
            </div>
        @else
            <!-- Campo editable cuando se está creando -->
            <x-form-number
                name="stock"
                label="Stock Disponible"
                :value="0"
                :required="true"
                :min="0"
                icon="fas fa-boxes"
                placeholder="0"
                helpText="Cantidad de ejemplares disponibles"
            />
        @endif

        <!-- Botones de Acción -->
        <div class="flex gap-3 pt-4 border-t border-gray-200">
            <x-button type="submit" variant="primary" icon="fas fa-save">
                {{ $submitText }}
            </x-button>
            <x-button type="button" variant="secondary" icon="fas fa-times" onclick="window.location='{{ route('inventario.index') }}'">
                Cancelar
            </x-button>
        </div>
    </div>
</form>
