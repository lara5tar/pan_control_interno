@extends('layouts.app')

@section('title', 'Detalle del Sub-Inventario')

@section('content')
<x-page-layout 
    title="Sub-Inventario #{{ $subinventario->id }}"
    description="Detalle del sub-inventario"
    button-text="Volver a Sub-Inventarios"
    button-icon="fas fa-arrow-left"
    :button-route="route('subinventarios.index')"
>
    <!-- Información del sub-inventario -->
    <x-card class="mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">
                    {{ $subinventario->descripcion ?: 'Sub-Inventario #' . $subinventario->id }}
                </h3>
                <div class="space-y-1 text-sm text-gray-600">
                    <p><i class="fas fa-calendar mr-2"></i><strong>Fecha:</strong> {{ $subinventario->fecha_subinventario->format('d/m/Y') }}</p>
                    <p><i class="fas fa-user mr-2"></i><strong>Usuario:</strong> {{ $subinventario->usuario }}</p>
                    <p><i class="fas fa-clock mr-2"></i><strong>Creado:</strong> {{ $subinventario->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            
            <span class="px-3 py-2 inline-flex text-sm leading-5 font-semibold rounded-full {{ $subinventario->getBadgeColor() }}">
                <i class="{{ $subinventario->getIcon() }} mr-2"></i>
                {{ $subinventario->getEstadoLabel() }}
            </span>
        </div>

        @if($subinventario->observaciones)
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-700">
                    <i class="fas fa-comment mr-2 text-gray-500"></i>
                    <strong>Observaciones:</strong> {{ $subinventario->observaciones }}
                </p>
            </div>
        @endif
    </x-card>

    <!-- Libros en Sub-Inventario -->
    <x-card class="mb-6">
        <div class="mb-4 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-book mr-2 text-blue-600"></i>Libros en Sub-Inventario
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    Total: {{ $subinventario->getTotalLibros() }} libros - {{ $subinventario->getTotalUnidades() }} unidades
                </p>
            </div>
            
            <!-- Botones de exportación -->
            @if($subinventario->libros->count() > 0)
                <div class="flex gap-3">
                    <x-button 
                        type="button" 
                        variant="success" 
                        icon="fas fa-file-excel"
                        onclick="window.location='{{ route('subinventarios.libros.export.excel', $subinventario) }}'"
                    >
                        Exportar Excel
                    </x-button>
                    
                    <x-button 
                        type="button" 
                        variant="danger" 
                        icon="fas fa-file-pdf"
                        onclick="window.location='{{ route('subinventarios.libros.export.pdf', $subinventario) }}'"
                    >
                        Exportar PDF
                    </x-button>
                </div>
            @endif
        </div>

        @if($subinventario->libros->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Libro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad en Sub-Inventario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock Actual</th>
                            @if($subinventario->estado === 'activo')
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($subinventario->libros as $libro)
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
                                    @if($libro->stock_subinventario > 0)
                                        <span class="text-xs text-gray-400">({{ $libro->stock_subinventario }} en sub-inventarios)</span>
                                    @endif
                                </td>
                                @if($subinventario->estado === 'activo')
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
                <p class="text-gray-500 text-lg">No hay libros en este sub-inventario</p>
            </div>
        @endif
    </x-card>

    <!-- Usuarios Asignados -->
    <x-card class="bg-blue-50 border-2 border-blue-300 shadow-lg">
        <div class="mb-4 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold text-blue-900 flex items-center">
                    <i class="fas fa-users mr-2"></i>Usuarios Asignados ({{ $usuariosAsignados->count() }})
                </h3>
                <p class="text-sm text-blue-700 mt-1">
                    Usuarios con acceso a este sub-inventario
                </p>
            </div>
            <a href="{{ route('subinventarios.usuarios', $subinventario) }}" 
               class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm shadow-md border-2 border-purple-800">
                <i class="fas fa-users-cog mr-2"></i>Gestionar Usuarios
            </a>
        </div>

        @if($usuariosAsignados && $usuariosAsignados->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-blue-300 border-2 border-blue-200">
                    <thead class="bg-blue-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-blue-900 uppercase">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-blue-900 uppercase">Fecha Asignación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-blue-900 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-blue-200">
                        @foreach($usuariosAsignados as $usuario)
                            <tr class="hover:bg-blue-100">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-purple-500 flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $usuario->nombre_congregante }}</div>
                                            <div class="text-xs text-gray-500">Código: {{ $usuario->cod_congregante }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ \Carbon\Carbon::parse($usuario->created_at)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <form action="{{ route('subinventarios.remove-user', $subinventario) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('¿Eliminar asignación de {{ $usuario->nombre_congregante }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="cod_congregante" value="{{ $usuario->cod_congregante }}">
                                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 shadow">
                                            <i class="fas fa-trash mr-1"></i>Remover
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 bg-white rounded-lg border-2 border-blue-200">
                <i class="fas fa-users-slash text-blue-400 text-5xl mb-4"></i>
                <p class="text-blue-700 text-lg font-medium">No hay usuarios asignados a este sub-inventario</p>
                <p class="text-blue-600 text-sm mt-2">Haz clic en "Asignar Usuario" para agregar usuarios</p>
            </div>
        @endif
    </x-card>

    <!-- Acciones -->
    @if($subinventario->estado === 'activo')
        <x-card title="Acciones" class="lg:w-1/2 lg:ml-auto">
            <div class="space-y-3">
                <x-button variant="warning" icon="fas fa-edit" href="{{ route('subinventarios.edit', $subinventario) }}" class="w-full justify-center">
                    Editar
                </x-button>

                <x-button variant="info" icon="fas fa-users" href="{{ route('subinventarios.usuarios', $subinventario) }}" class="w-full justify-center">
                    Gestionar Usuarios
                </x-button>

                <form action="{{ route('subinventarios.completar', $subinventario) }}" 
                      method="POST"
                      onsubmit="return confirm('¿Completar este sub-inventario? Esto indica que se vendió todo el inventario del sub-inventario.')">
                    @csrf
                    <x-button type="submit" variant="success" icon="fas fa-check-circle" class="w-full justify-center">
                        Marcar como Completado
                    </x-button>
                </form>

                <form action="{{ route('subinventarios.cancelar', $subinventario) }}" 
                      method="POST"
                      onsubmit="return confirm('¿Cancelar este sub-inventario? El inventario se devolverá.')">
                    @csrf
                    <x-button type="submit" variant="danger" icon="fas fa-times-circle" class="w-full justify-center">
                        Cancelar Sub-Inventario
                    </x-button>
                </form>
            </div>
        </x-card>
    @endif
</x-page-layout>

<!-- Modal para asignar usuario -->
<div id="modalAsignar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border-4 border-purple-500 w-96 shadow-2xl rounded-md bg-gradient-to-br from-purple-50 to-blue-50">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-purple-900 mb-4">
                <i class="fas fa-user-plus mr-2 text-purple-600"></i>Asignar Usuario
            </h3>
            
            <form action="{{ route('subinventarios.assign-user', $subinventario) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-900 mb-2">
                        Nombre del Usuario *
                    </label>
                    <input type="text" 
                           name="user_name" 
                           required
                           class="w-full rounded-lg border-2 border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 px-4 py-2"
                           placeholder="Nombre completo del congregante">
                    <p class="text-xs text-gray-600 mt-1">
                        <i class="fas fa-info-circle"></i> Ingresa el nombre completo del congregante que aparece en el sistema de usuarios
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" 
                            onclick="cerrarModalAsignar()"
                            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 border-2 border-gray-700 shadow">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 border-2 border-purple-800 shadow">
                        <i class="fas fa-check mr-2"></i>Asignar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
    function mostrarModalAsignar() {
        document.getElementById('modalAsignar').classList.remove('hidden');
    }

    function cerrarModalAsignar() {
        document.getElementById('modalAsignar').classList.add('hidden');
    }

    // Cerrar modal al hacer clic fuera
    document.getElementById('modalAsignar').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalAsignar();
        }
    });

    function mostrarModalDevolver(libroId, libroNombre, cantidadApartada) {
        document.getElementById('libroId').value = libroId;
        document.getElementById('libroNombre').textContent = libroNombre;
        document.getElementById('cantidadApartada').textContent = cantidadApartada;
        document.getElementById('cantidadDevolver').max = cantidadApartada;
        document.getElementById('cantidadDevolver').value = 1;
        
        const form = document.getElementById('formDevolver');
        form.action = "{{ route('subinventarios.devolver-parcial', $subinventario) }}";
        
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
