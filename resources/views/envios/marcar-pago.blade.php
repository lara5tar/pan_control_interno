@extends('layouts.app')

@section('title', 'Marcar Pago de Envío')

@section('page-title', 'Marcar Pago de Envío')
@section('page-description', 'Registrar pago a FedEx')

@section('content')
<x-page-layout 
    title="Marcar Envío como Pagado"
    :description="'Envío #' . $envio->id"
    button-text="Volver al Envío"
    button-icon="fas fa-arrow-left"
    :button-route="route('envios.show', $envio)"
>
    <form action="{{ route('envios.marcar-pagado', $envio) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulario de Pago -->
            <div class="lg:col-span-2">
                <x-card title="Información del Pago">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Fecha de Pago -->
                        <div class="lg:col-span-2">
                            <label for="fecha_pago" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Pago <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-400">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <input 
                                    type="date" 
                                    name="fecha_pago" 
                                    id="fecha_pago"
                                    value="{{ old('fecha_pago', date('Y-m-d')) }}"
                                    required
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('fecha_pago') border-red-500 @enderror"
                                >
                            </div>
                            @error('fecha_pago')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Referencia de Pago -->
                        <div class="lg:col-span-2">
                            <label for="referencia_pago" class="block text-sm font-medium text-gray-700 mb-2">
                                Referencia de Pago
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-400">
                                    <i class="fas fa-hashtag"></i>
                                </span>
                                <input 
                                    type="text" 
                                    name="referencia_pago" 
                                    id="referencia_pago"
                                    value="{{ old('referencia_pago') }}"
                                    placeholder="Ej: TRX123456789"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('referencia_pago') border-red-500 @enderror"
                                >
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                <i class="fas fa-info-circle"></i> Número de referencia, transacción o folio del pago
                            </p>
                            @error('referencia_pago')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Comprobante de Pago -->
                        <div class="lg:col-span-2">
                            <label for="comprobante_pago" class="block text-sm font-medium text-gray-700 mb-2">
                                Comprobante de Pago
                            </label>
                            <div class="relative">
                                <input 
                                    type="file" 
                                    name="comprobante_pago" 
                                    id="comprobante_pago"
                                    accept="image/*,.pdf"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('comprobante_pago') border-red-500 @enderror"
                                >
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                <i class="fas fa-info-circle"></i> Opcional. Puedes subir una foto del depósito o comprobante bancario.
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                Formatos permitidos: JPG, PNG, PDF (Máximo 5MB)
                            </p>
                            @error('comprobante_pago')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Vista previa del archivo -->
                        <div class="lg:col-span-2">
                            <div id="preview-container" class="hidden mt-4">
                                <p class="text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-eye text-gray-400"></i>
                                    Vista Previa
                                </p>
                                <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                                    <img id="preview-image" class="hidden max-h-64 mx-auto rounded" alt="Vista previa">
                                    <div id="preview-file" class="hidden text-center">
                                        <i class="fas fa-file-pdf text-red-500 text-5xl mb-2"></i>
                                        <p id="file-name" class="text-sm text-gray-600"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </x-card>
            </div>

            <!-- Resumen del Envío -->
            <div class="lg:col-span-1">
                <x-card title="Resumen del Envío">
                    <div class="space-y-4">
                        
                        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                            <span class="text-sm text-gray-600">ID de Envío:</span>
                            <span class="text-sm font-bold text-gray-900">#{{ $envio->id }}</span>
                        </div>

                        @if($envio->guia)
                            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                                <span class="text-sm text-gray-600">Guía:</span>
                                <span class="text-sm font-mono font-medium text-gray-900">{{ $envio->guia }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                            <span class="text-sm text-gray-600">Fecha de Envío:</span>
                            <span class="text-sm font-medium text-gray-900">
                                {{ $envio->fecha_envio->format('d/m/Y') }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                            <span class="text-sm text-gray-600">Total Ventas:</span>
                            <span class="text-sm font-medium text-gray-900">
                                ${{ number_format($envio->calcularTotalVentas(), 2) }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                            <span class="text-sm text-gray-600">Estado Actual:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $envio->getBadgeColor() }}">
                                <i class="{{ $envio->getIcon() }} mr-1"></i>
                                {{ $envio->getEstadoLabel() }}
                            </span>
                        </div>

                        <!-- Monto a Pagar - Destacado -->
                        <div class="mt-4 p-4 bg-green-50 border-2 border-green-200 rounded-lg">
                            <div class="flex flex-col items-center text-center">
                                <i class="fas fa-money-bill-wave text-green-600 text-3xl mb-2"></i>
                                <span class="text-sm text-gray-600 mb-1">Monto a Pagar a FedEx</span>
                                <span class="text-3xl font-bold text-green-600">
                                    ${{ number_format($envio->monto_a_pagar, 2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Info adicional -->
                        <div class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-400 rounded">
                            <p class="text-xs text-blue-800">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Nota:</strong> Al confirmar, este envío quedará marcado como "Pagado" y se registrará la fecha del pago.
                            </p>
                        </div>

                    </div>
                </x-card>
            </div>
        </div>

        <!-- Botones de Acción -->
        <x-card class="mt-6">
            <div class="flex flex-wrap justify-between gap-4">
                <x-button 
                    type="button"
                    variant="secondary" 
                    icon="fas fa-times"
                    onclick="window.location='{{ route('envios.show', $envio) }}'"
                >
                    Cancelar
                </x-button>

                <x-button 
                    type="submit"
                    variant="success" 
                    icon="fas fa-check-circle"
                >
                    Confirmar Pago
                </x-button>
            </div>
        </x-card>

    </form>
</x-page-layout>

@push('scripts')
<script>
    // Vista previa del archivo seleccionado
    document.getElementById('comprobante_pago').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');
        const previewFile = document.getElementById('preview-file');
        const fileName = document.getElementById('file-name');

        if (file) {
            previewContainer.classList.remove('hidden');

            if (file.type.startsWith('image/')) {
                // Es una imagen
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewImage.classList.remove('hidden');
                    previewFile.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            } else if (file.type === 'application/pdf') {
                // Es un PDF
                previewImage.classList.add('hidden');
                previewFile.classList.remove('hidden');
                fileName.textContent = file.name;
            }
        } else {
            previewContainer.classList.add('hidden');
            previewImage.classList.add('hidden');
            previewFile.classList.add('hidden');
        }
    });
</script>
@endpush

@endsection
