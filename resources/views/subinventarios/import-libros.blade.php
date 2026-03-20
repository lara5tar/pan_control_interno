@extends('layouts.app')

@section('title', 'Importar Libros - Sub-Inventario')

@section('content')
<x-page-layout 
    title="Importar Libros en Lote"
    description="Importa múltiples libros al sub-inventario usando Excel"
    button-text="Volver a Sub-Inventario"
    button-icon="fas fa-arrow-left"
    :button-route="route('subinventarios.show', $subinventario)"
>
    <x-card>
        <div class="mb-6">
            <div class="text-sm text-gray-600 mb-4">
                <p><i class="fas fa-calendar mr-2 text-blue-600"></i><strong>Fecha:</strong> {{ $subinventario->fecha_subinventario->format('d/m/Y') }}</p>
                <p class="mt-2"><i class="fas fa-book mr-2 text-blue-600"></i><strong>Estado:</strong> 
                    <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full {{ $subinventario->estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($subinventario->estado) }}
                    </span>
                </p>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-6">
                <i class="fas fa-file-excel mr-2 text-blue-600"></i> Cargar archivo Excel
            </h3>

            <form action="{{ route('subinventarios.import', $subinventario) }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf

                <div class="mb-6">
                    <label for="archivo" class="block text-sm font-bold text-gray-700 mb-2">
                        Selecciona tu archivo Excel:
                    </label>
                    <div class="relative">
                        <input 
                            type="file" 
                            class="block w-full text-sm text-gray-500 border-2 border-dashed border-gray-300 rounded-lg p-4 cursor-pointer hover:border-blue-400 @error('archivo') border-red-500 @enderror" 
                            id="archivo" 
                            name="archivo"
                            accept=".xlsx,.xls,.csv"
                            required
                        >
                    </div>
                    @error('archivo')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-2">
                        <i class="fas fa-info-circle mr-1"></i><strong>Formatos aceptados:</strong> Excel (.xlsx, .xls) o CSV | <strong>Columnas:</strong> ID (columna A), Cantidad (columna B)
                    </p>
                </div>

                <div class="flex gap-3">
                    <x-button type="submit" variant="success" icon="fas fa-upload" class="flex-1">
                        Importar Libros
                    </x-button>
                    <x-button 
                        type="button"
                        variant="secondary" 
                        icon="fas fa-download"
                        onclick="window.location='{{ route('subinventarios.download-template', $subinventario) }}'"
                        class="flex-1"
                    >
                        Descargar Plantilla
                    </x-button>
                </div>
            </form>
        </div>
    </x-card>
</x-page-layout>
@endsection
