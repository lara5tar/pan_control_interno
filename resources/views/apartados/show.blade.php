@extends('layouts.app')

@section('title', 'Detalle del Apartado')

@section('content')
<x-page-layout 
    title="Apartado #{{ $apartado->id }}"
    description="Detalle del apartado de inventario"
    button-text="Volver a Apartados"
    button-icon="fas fa-arrow-left"
    :button-route="route('apartados.index')"
>
    <!-- Información del apartado -->
    <x-card class="mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">
                    {{ $apartado->descripcion ?: 'Apartado #' . $apartado->id }}
                </h3>
                <div class="space-y-1 text-sm text-gray-600">
                    <p><i class="fas fa-calendar mr-2"></i><strong>Fecha:</strong> {{ $apartado->fecha_apartado->format('d/m/Y') }}</p>
                    <p><i class="fas fa-user mr-2"></i><strong>Usuario:</strong> {{ $apartado->usuario }}</p>
                    <p><i class="fas fa-clock mr-2"></i><strong>Creado:</strong> {{ $apartado->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            
            <span class="px-3 py-2 inline-flex text-sm leading-5 font-semibold rounded-full {{ $apartado->getBadgeColor() }}">
                <i class="{{ $apartado->getIcon() }} mr-2"></i>
                {{ $apartado->getEstadoLabel() }}
            </span>
        </div>

        @if($apartado->observaciones)
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-700">
                    <i class="fas fa-comment mr-2 text-gray-500"></i>
                    <strong>Observaciones:</strong> {{ $apartado->observaciones }}
                </p>
            </div>
        @endif
    </x-card>

    <!-- Acciones -->
    @if($apartado->estado === 'activo')
        <x-card class="mb-6">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('apartados.edit', $apartado) }}" 
                   class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700">
                    <i class="fas fa-edit mr-2"></i>Editar Apartado
                </a>

                <form action="{{ route('apartados.completar', $apartado) }}" 
                      method="POST" 
                      class="inline"
                      onsubmit="return confirm('¿Completar este apartado? Esto indica que se vendió todo el inventario apartado.')">
                    @csrf
                    <button type="submit" 
                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-check-circle mr-2"></i>Marcar como Completado
                    </button>
                </form>

                <form action="{{ route('apartados.cancelar', $apartado) }}" 
                      method="POST" 
                      class="inline"
                      onsubmit="return confirm('¿Cancelar este apartado? El inventario se devolverá.')">
                    @csrf
                    <button type="submit" 
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                        <i class="fas fa-times-circle mr-2"></i>Cancelar Apartado
                    </button>
                </form>
            </div>
        </x-card>
    @endif

    <!-- Libros apartados -->
    <x-card>
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-book mr-2 text-blue-600"></i>Libros Apartados
            </h3>
            <p class="text-sm text-gray-600 mt-1">
                Total: {{ $apartado->getTotalLibros() }} libros - {{ $apartado->getTotalUnidades() }} unidades
            </p>
        </div>

        @if($apartado->libros->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Libro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad Apartada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock Actual</th>
                            @if($apartado->estado === 'activo')
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($apartado->libros as $libro)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $libro->nombre }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $libro->codigo_barras }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ number_format($libro->precio, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $libro->pivot->cantidad }} unidades
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $libro->stock }} 
                                    @if($libro->stock_apartado > 0)
                                        <span class="text-xs text-gray-400">({{ $libro->stock_apartado }} apartados)</span>
                                    @endif
                                </td>
                                @if($apartado->estado === 'activo')
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button onclick="mostrarModalDevolver({{ $libro->id }}, '{{ $libro->nombre }}', {{ $libro->pivot->cantidad }})"
                                                class="text-orange-600 hover:text-orange-900"
                                                title="Devolver parcialmente">
                                            <i class="fas fa-undo mr-1"></i>Devolver
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg">No hay libros en este apartado</p>
            </div>
        @endif
    </x-card>
</x-page-layout>

<!-- Modal para devolver parcialmente -->
<div id="modalDevolver" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Devolver Stock</h3>
            <p class="text-sm text-gray-600 mb-4">
                <strong id="libroNombre"></strong><br>
                Cantidad apartada: <span id="cantidadApartada"></span>
            </p>
            
            <form id="formDevolver" action="" method="POST">
                @csrf
                <input type="hidden" name="libro_id" id="libroId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Cantidad a devolver
                    </label>
                    <input type="number" 
                           name="cantidad" 
                           id="cantidadDevolver"
                           min="1" 
                           required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" 
                            onclick="cerrarModalDevolver()"
                            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">
                        <i class="fas fa-undo mr-2"></i>Devolver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function mostrarModalDevolver(libroId, libroNombre, cantidadApartada) {
        document.getElementById('libroId').value = libroId;
        document.getElementById('libroNombre').textContent = libroNombre;
        document.getElementById('cantidadApartada').textContent = cantidadApartada;
        document.getElementById('cantidadDevolver').max = cantidadApartada;
        document.getElementById('cantidadDevolver').value = 1;
        
        const form = document.getElementById('formDevolver');
        form.action = "{{ route('apartados.devolver-parcial', $apartado) }}";
        
        document.getElementById('modalDevolver').classList.remove('hidden');
    }

    function cerrarModalDevolver() {
        document.getElementById('modalDevolver').classList.add('hidden');
    }

    // Cerrar modal al hacer clic fuera
    document.getElementById('modalDevolver').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalDevolver();
        }
    });
</script>
@endpush
@endsection
