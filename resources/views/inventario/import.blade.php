@extends('layouts.app')

@section('title', 'Importar Libros desde Excel')

@section('page-title', 'Importar Libros desde Excel')
@section('page-description', 'Carga masiva de libros mediante archivo Excel')

@section('content')
<div class="space-y-6">
    <!-- Encabezado -->
    <x-page-header 
        title="Importación Masiva de Libros"
        description="Sube un archivo Excel con múltiples libros para registrarlos de forma automática"
        button-text="Volver al Inventario"
        button-icon="fas fa-arrow-left"
        :button-route="route('inventario.index')"
    />

    <!-- Mensajes de advertencia con errores -->
    @if(session('warning') && session('errors_list'))
        <x-alert type="warning">
            <div class="space-y-2">
                <p class="font-semibold">{{ session('warning') }}</p>
                <details class="mt-2">
                    <summary class="cursor-pointer font-medium hover:text-yellow-800">
                        Ver detalles de errores ({{ count(session('errors_list')) }})
                    </summary>
                    <ul class="mt-2 ml-4 list-disc space-y-1 text-sm">
                        @foreach(session('errors_list') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </details>
            </div>
        </x-alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Instrucciones y descarga de plantilla -->
        <x-card>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-info-circle text-blue-600"></i>
                Instrucciones
            </h3>
            
            <div class="space-y-4">
                <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded">
                    <h4 class="font-semibold text-blue-800 mb-2">Pasos para importar:</h4>
                    <ol class="list-decimal list-inside space-y-2 text-sm text-blue-900">
                        <li>Descarga la plantilla de Excel haciendo clic en el botón de abajo</li>
                        <li>Completa la plantilla con los datos de los libros</li>
                        <li>Guarda el archivo Excel en tu computadora</li>
                        <li>Sube el archivo usando el formulario de la derecha</li>
                        <li>Revisa los resultados de la importación</li>
                    </ol>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <h4 class="font-semibold text-yellow-800 mb-2">⚠️ Importante:</h4>
                    <ul class="list-disc list-inside space-y-1 text-sm text-yellow-900">
                        <li>No modifiques los encabezados de las columnas</li>
                        <li>Todos los campos marcados con (*) son obligatorios</li>
                        <li>El código de barras es opcional (puedes dejarlo vacío)</li>
                        <li>El formato de precio debe ser numérico (ej: 150.50)</li>
                        <li>La cantidad debe ser un número entero positivo</li>
                    </ul>
                </div>

                <!-- Botón de descarga de plantilla -->
                <div class="pt-4">
                    <a href="{{ route('inventario.download-template') }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                        <i class="fas fa-download"></i>
                        Descargar Plantilla Excel
                    </a>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-file-excel text-green-600"></i>
                        Archivo: plantilla_libros.xlsx
                    </p>
                </div>
            </div>
        </x-card>

        <!-- Formulario de subida -->
        <x-card>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-upload text-gray-600"></i>
                Subir Archivo Excel
            </h3>

            <form action="{{ route('inventario.import.process') }}" 
                  method="POST" 
                  enctype="multipart/form-data"
                  id="importForm"
                  class="space-y-6">
                @csrf

                <!-- Zona de arrastre de archivo -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Archivo Excel <span class="text-red-500">*</span>
                    </label>
                    
                    <div class="mt-2 flex justify-center px-6 pt-8 pb-8 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors bg-gray-50"
                         id="dropZone">
                        <div class="space-y-2 text-center">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none px-2">
                                    <span>Selecciona un archivo</span>
                                    <input id="file-upload" 
                                           name="file" 
                                           type="file" 
                                           class="sr-only" 
                                           accept=".xlsx,.xls"
                                           required>
                                </label>
                                <p class="pl-1">o arrastra aquí</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                Solo archivos Excel (.xlsx, .xls) hasta 5MB
                            </p>
                        </div>
                    </div>

                    <!-- Nombre del archivo seleccionado -->
                    <div id="fileName" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-file-excel text-green-600"></i>
                                <span class="text-sm font-medium text-gray-700" id="fileNameText"></span>
                                <span class="text-xs text-gray-500" id="fileSizeText"></span>
                            </div>
                            <button type="button" 
                                    id="removeFile"
                                    class="text-red-600 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    @error('file')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Opciones adicionales -->
                <div class="space-y-3 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" 
                               name="skip_errors" 
                               value="1"
                               checked
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700">
                            Continuar importación si hay errores en algunas filas
                        </span>
                    </label>
                </div>

                <!-- Botones de acción -->
                <div class="flex gap-3 pt-4">
                    <x-button type="submit" variant="primary" icon="fas fa-file-import">
                        Importar Libros
                    </x-button>
                    
                    <x-button type="button" 
                              variant="secondary" 
                              icon="fas fa-times"
                              onclick="window.location='{{ route('inventario.index') }}'">
                        Cancelar
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>

<x-loading />

@push('scripts')
<script>
    // Manejo de drag & drop
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('file-upload');
    const fileName = document.getElementById('fileName');
    const fileNameText = document.getElementById('fileNameText');
    const fileSizeText = document.getElementById('fileSizeText');
    const removeFileBtn = document.getElementById('removeFile');

    // Prevenir comportamiento por defecto
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Resaltar zona de drop
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });
    });

    // Manejar drop
    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            displayFileName(files[0]);
        }
    });

    // Manejar selección de archivo
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            displayFileName(e.target.files[0]);
        }
    });

    // Mostrar nombre del archivo
    function displayFileName(file) {
        const size = (file.size / 1024 / 1024).toFixed(2);
        fileNameText.textContent = file.name;
        fileSizeText.textContent = `(${size} MB)`;
        fileName.classList.remove('hidden');
    }

    // Remover archivo
    removeFileBtn.addEventListener('click', () => {
        fileInput.value = '';
        fileName.classList.add('hidden');
    });
</script>
@endpush
@endsection
