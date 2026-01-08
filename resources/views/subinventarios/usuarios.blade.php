@extends('layouts.app')

@section('title', 'Gestionar Congregantes del Sub-Inventario')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Encabezado -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestionar Congregantes</h1>
            <p class="text-gray-600 mt-1">Sub-Inventario #{{ $subinventario->id }}</p>
        </div>
        <a href="{{ route('subinventarios.show', $subinventario) }}" 
           class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Volver al Sub-Inventario
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Información del sub-inventario -->
    <div class="bg-white rounded-lg p-6 mb-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ $subinventario->descripcion ?: 'Sub-Inventario #' . $subinventario->id }}
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    <i class="fas fa-calendar mr-2"></i>{{ $subinventario->fecha_subinventario->format('d/m/Y') }}
                    <span class="ml-4"><i class="fas fa-user mr-2"></i>{{ $subinventario->usuario }}</span>
                </p>
            </div>
            <span class="px-3 py-2 inline-flex text-sm leading-5 font-semibold rounded-full {{ $subinventario->getBadgeColor() }}">
                {{ $subinventario->getEstadoLabel() }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Formulario para agregar congregante -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-user-plus mr-2 text-purple-600"></i>Asignar Congregante
            </h3>

            <form action="{{ route('subinventarios.assign-user', $subinventario) }}" method="POST" id="assignForm">
                @csrf
                
                <!-- Campo oculto para el código del congregante -->
                <input type="hidden" name="cod_congregante" id="cod_congregante" required>
                <input type="hidden" name="nombre_congregante" id="nombre_congregante" required>
                
                <div class="mb-4 relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Buscar Congregante <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="search_congregante"
                           autocomplete="off"
                           class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 px-4 py-2"
                           placeholder="Escribe para buscar por nombre...">
                    
                    <!-- Resultados de búsqueda -->
                    <div id="search_results" 
                         class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-64 overflow-y-auto hidden">
                    </div>
                    
                    <!-- Congregante seleccionado -->
                    <div id="selected_congregante" class="mt-3 hidden">
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-user-check text-purple-600 mr-2"></i>
                                <div>
                                    <p class="font-medium text-gray-900" id="selected_nombre"></p>
                                    <p class="text-xs text-gray-600" id="selected_info"></p>
                                </div>
                            </div>
                            <button type="button" onclick="clearSelection()" 
                                    class="text-red-600 hover:text-red-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <p class="text-xs text-gray-600 mt-2">
                        <i class="fas fa-info-circle"></i> Escribe al menos 2 letras para buscar
                    </p>
                    @error('cod_congregante')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" 
                        id="submit_btn"
                        disabled
                        class="w-full bg-purple-600 text-white px-4 py-3 rounded-lg hover:bg-purple-700 font-medium disabled:bg-gray-400 disabled:cursor-not-allowed">
                    <i class="fas fa-check mr-2"></i>Asignar Congregante
                </button>
            </form>
        </div>

        <!-- Lista de congregantes asignados -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-users mr-2 text-blue-600"></i>Congregantes Asignados 
                <span class="text-sm font-normal text-gray-600">({{ $usuariosAsignados->count() }})</span>
            </h3>

            @if($usuariosAsignados && $usuariosAsignados->count() > 0)
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($usuariosAsignados as $usuario)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition border border-gray-200">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-purple-500 flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $usuario->nombre_congregante }}</p>
                                    <p class="text-xs text-gray-500">
                                        Código: {{ $usuario->cod_congregante }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <i class="fas fa-clock mr-1"></i>Asignado: {{ \Carbon\Carbon::parse($usuario->created_at)->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                            <form action="{{ route('subinventarios.remove-user', $subinventario) }}" 
                                  method="POST" 
                                  class="inline"
                                  onsubmit="return confirm('¿Eliminar asignación de {{ $usuario->nombre_congregante }}?')">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="cod_congregante" value="{{ $usuario->cod_congregante }}">
                                <button type="submit" 
                                        class="text-white bg-red-500 hover:bg-red-600 p-2 rounded transition"
                                        title="Remover congregante">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
                    <i class="fas fa-users-slash text-gray-400 text-5xl mb-4"></i>
                    <p class="text-gray-700 font-medium">No hay congregantes asignados</p>
                    <p class="text-gray-500 text-sm mt-2">Usa el buscador para asignar congregantes</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
let searchTimeout;
const searchInput = document.getElementById('search_congregante');
const searchResults = document.getElementById('search_results');
const selectedDiv = document.getElementById('selected_congregante');
const submitBtn = document.getElementById('submit_btn');

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const termino = this.value.trim();
    
    if (termino.length < 2) {
        searchResults.classList.add('hidden');
        return;
    }
    
    searchTimeout = setTimeout(() => {
        buscarCongregantes(termino);
    }, 500);
});

function buscarCongregantes(termino) {
    searchResults.innerHTML = '<div class="p-4 text-center text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>Buscando...</div>';
    searchResults.classList.remove('hidden');
    
    const url = `{{ route('subinventarios.buscar-congregantes') }}?termino=${encodeURIComponent(termino)}`;
    console.log('Buscando en URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            
            if (data.error) {
                searchResults.innerHTML = `<div class="p-4 text-center text-red-500">Error: ${data.message || 'Error al buscar'}</div>`;
                return;
            }
            
            if (!data.congregantes || data.congregantes.length === 0) {
                searchResults.innerHTML = '<div class="p-4 text-center text-gray-500">No se encontraron congregantes</div>';
                return;
            }
            
            let html = '';
            data.congregantes.forEach(congregante => {
                html += `
                    <div class="p-3 hover:bg-purple-50 cursor-pointer border-b border-gray-100 last:border-0" 
                         onclick='selectCongregante(${JSON.stringify(congregante)})'>
                        <p class="font-medium text-gray-900">${congregante.nombre_completo}</p>
                        <p class="text-xs text-gray-600">
                            Código: ${congregante.cod_congregante}
                            ${congregante.ciudad ? ' • ' + congregante.ciudad : ''}
                            ${congregante.celular ? ' • ' + congregante.celular : ''}
                        </p>
                    </div>
                `;
            });
            
            searchResults.innerHTML = html;
        })
        .catch(error => {
            console.error('Error completo:', error);
            searchResults.innerHTML = `<div class="p-4 text-center text-red-500">Error al conectar<br><small class="text-xs">${error.message}</small></div>`;
        });
}

function selectCongregante(congregante) {
    document.getElementById('cod_congregante').value = congregante.cod_congregante;
    document.getElementById('nombre_congregante').value = congregante.nombre_completo;
    
    document.getElementById('selected_nombre').textContent = congregante.nombre_completo;
    document.getElementById('selected_info').textContent = `Código: ${congregante.cod_congregante}`;
    
    selectedDiv.classList.remove('hidden');
    searchInput.value = '';
    searchResults.classList.add('hidden');
    submitBtn.disabled = false;
}

function clearSelection() {
    document.getElementById('cod_congregante').value = '';
    document.getElementById('nombre_congregante').value = '';
    selectedDiv.classList.add('hidden');
    submitBtn.disabled = true;
    searchInput.value = '';
}

// Cerrar resultados al hacer click fuera
document.addEventListener('click', function(event) {
    if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
        searchResults.classList.add('hidden');
    }
});
</script>
@endsection
