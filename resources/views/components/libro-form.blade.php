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
        <div>
            <label for="codigo_barras" class="block text-sm font-medium text-gray-700 mb-2">
                Código de Barras
            </label>
            <div class="flex gap-2">
                <div class="flex-1 relative">
                    <span class="absolute left-3 top-2.5 text-gray-400">
                        <i class="fas fa-barcode"></i>
                    </span>
                    <input 
                        type="text" 
                        name="codigo_barras" 
                        id="codigo_barras" 
                        value="{{ old('codigo_barras', $libro?->codigo_barras ?? '') }}"
                        placeholder="Ej: 9781234567890 (Opcional)"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all @error('codigo_barras') border-red-500 @enderror"
                    >
                </div>
                <button 
                    type="button"
                    id="generateBarcodeBtn"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors flex items-center gap-2 whitespace-nowrap"
                    title="Generar código aleatorio"
                >
                    <i class="fas fa-sync-alt"></i>
                    Generar
                </button>
            </div>
            <p class="mt-1 text-sm text-gray-500">
                <i class="fas fa-info-circle"></i> Código único del libro (ISBN o código de barras). Usa el botón "Generar" para crear uno aleatorio.
            </p>
            @error('codigo_barras')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const generateBtn = document.getElementById('generateBarcodeBtn');
        const barcodeInput = document.querySelector('input[name="codigo_barras"]');

        if (generateBtn && barcodeInput) {
            generateBtn.addEventListener('click', async function() {
                // Deshabilitar botón y mostrar loading
                generateBtn.disabled = true;
                const originalHTML = generateBtn.innerHTML;
                generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

                try {
                    const response = await fetch('{{ route("inventario.generate-barcode") }}');
                    const data = await response.json();
                    
                    if (data.codigo) {
                        barcodeInput.value = data.codigo;
                        
                        // Animación de éxito
                        barcodeInput.classList.add('border-green-500', 'bg-green-50');
                        setTimeout(() => {
                            barcodeInput.classList.remove('border-green-500', 'bg-green-50');
                        }, 1000);
                    }
                } catch (error) {
                    console.error('Error al generar código:', error);
                    alert('Error al generar el código de barras');
                } finally {
                    // Restaurar botón
                    generateBtn.disabled = false;
                    generateBtn.innerHTML = originalHTML;
                }
            });
        }
    });
</script>
@endpush

    </div>
</form>