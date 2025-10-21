@props([
    'name' => 'libro_id',
    'selected' => null,
    'required' => false,
    'libros' => []
])

<div class="relative" x-data="libroSelector{{ $name }}">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
        Libro @if($required)<span class="text-red-500">*</span>@endif
    </label>
    
    <!-- Input de búsqueda visible -->
    <div class="relative">
        <div class="relative">
            <span class="absolute left-3 top-3 text-gray-400">
                <i class="fas fa-search"></i>
            </span>
            <input 
                type="text" 
                id="{{ $name }}_search"
                placeholder="Buscar libro por nombre o código..."
                autocomplete="off"
                class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error($name) border-red-500 @enderror"
            >
            <button 
                type="button"
                id="{{ $name }}_clear"
                class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Dropdown de resultados -->
        <div 
            id="{{ $name }}_dropdown" 
            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-64 overflow-y-auto hidden"
        >
            <div id="{{ $name }}_results" class="divide-y divide-gray-100">
                <!-- Los resultados se insertarán aquí -->
            </div>
            <div id="{{ $name }}_no_results" class="hidden p-4 text-center text-gray-500">
                <i class="fas fa-search text-2xl mb-2 text-gray-300"></i>
                <p>No se encontraron libros</p>
            </div>
        </div>
    </div>
    
    <!-- Input hidden con el valor real -->
    <input 
        type="hidden" 
        name="{{ $name }}" 
        id="{{ $name }}"
        value="{{ old($name, $selected) }}"
        @if($required) required @endif
    >
    
    <!-- Libro seleccionado -->
    <div id="{{ $name }}_selected" class="mt-2 hidden">
        <div class="p-3 bg-primary-50 border border-primary-200 rounded-lg flex items-center justify-between">
            <div class="flex-1">
                <p class="font-medium text-gray-900" id="{{ $name }}_selected_nombre"></p>
                <p class="text-sm text-gray-600" id="{{ $name }}_selected_codigo"></p>
                <p class="text-sm text-gray-600">
                    Stock: <span class="font-semibold" id="{{ $name }}_selected_stock"></span> | 
                    Precio: <span class="font-semibold">$<span id="{{ $name }}_selected_precio"></span></span>
                </p>
            </div>
            <button 
                type="button" 
                id="{{ $name }}_remove"
                class="ml-3 text-red-600 hover:text-red-800"
            >
                <i class="fas fa-times-circle text-xl"></i>
            </button>
        </div>
    </div>
    
    @error($name)
        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
    @enderror
    
    <p id="{{ $name }}_stock_info" class="mt-1 text-sm text-gray-500 hidden">
        <i class="fas fa-info-circle"></i> Stock actual: <span id="{{ $name }}_stock_actual">0</span>
    </p>
</div>

<script>
(function() {
    const libros = @json($libros);
    const nameId = '{{ $name }}';
    const searchInput = document.getElementById(nameId + '_search');
    const dropdown = document.getElementById(nameId + '_dropdown');
    const resultsContainer = document.getElementById(nameId + '_results');
    const noResults = document.getElementById(nameId + '_no_results');
    const hiddenInput = document.getElementById(nameId);
    const selectedDiv = document.getElementById(nameId + '_selected');
    const selectedNombre = document.getElementById(nameId + '_selected_nombre');
    const selectedCodigo = document.getElementById(nameId + '_selected_codigo');
    const selectedStock = document.getElementById(nameId + '_selected_stock');
    const selectedPrecio = document.getElementById(nameId + '_selected_precio');
    const removeBtn = document.getElementById(nameId + '_remove');
    const clearBtn = document.getElementById(nameId + '_clear');
    const stockInfo = document.getElementById(nameId + '_stock_info');
    const stockActual = document.getElementById(nameId + '_stock_actual');

    let selectedLibro = null;

    // Función para normalizar texto (sin acentos, minúsculas)
    function normalizeText(text) {
        return text.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    // Función para buscar libros
    function searchLibros(query) {
        if (!query || query.length < 1) {
            return libros;
        }

        const normalizedQuery = normalizeText(query);
        
        return libros.filter(libro => {
            const nombre = normalizeText(libro.nombre);
            const codigo = normalizeText(libro.codigo_barras);
            
            return nombre.includes(normalizedQuery) || codigo.includes(normalizedQuery);
        });
    }

    // Función para renderizar resultados
    function renderResults(results) {
        resultsContainer.innerHTML = '';
        
        if (results.length === 0) {
            noResults.classList.remove('hidden');
            resultsContainer.classList.add('hidden');
            return;
        }

        noResults.classList.add('hidden');
        resultsContainer.classList.remove('hidden');

        results.forEach(libro => {
            const item = document.createElement('div');
            item.className = 'p-3 hover:bg-gray-50 cursor-pointer transition-colors';
            item.innerHTML = `
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">${libro.nombre}</p>
                        <p class="text-sm text-gray-600">ISBN: ${libro.codigo_barras}</p>
                    </div>
                    <div class="text-right ml-3">
                        <p class="text-sm font-semibold text-primary-600">Stock: ${libro.stock}</p>
                        <p class="text-sm text-gray-600">$${parseFloat(libro.precio).toFixed(2)}</p>
                    </div>
                </div>
            `;
            
            item.addEventListener('click', () => selectLibro(libro));
            resultsContainer.appendChild(item);
        });
    }

    // Función para seleccionar un libro
    function selectLibro(libro) {
        selectedLibro = libro;
        hiddenInput.value = libro.id;
        
        selectedNombre.textContent = libro.nombre;
        selectedCodigo.textContent = 'ISBN: ' + libro.codigo_barras;
        selectedStock.textContent = libro.stock;
        selectedPrecio.textContent = parseFloat(libro.precio).toFixed(2);
        
        selectedDiv.classList.remove('hidden');
        searchInput.value = '';
        dropdown.classList.add('hidden');
        
        // Mostrar stock info
        stockActual.textContent = libro.stock;
        stockInfo.classList.remove('hidden');

        // Disparar evento personalizado
        const event = new CustomEvent('libroSelected', { 
            detail: libro,
            bubbles: true 
        });
        document.getElementById(nameId).dispatchEvent(event);
    }

    // Función para limpiar selección
    function clearSelection() {
        selectedLibro = null;
        hiddenInput.value = '';
        selectedDiv.classList.add('hidden');
        searchInput.value = '';
        stockInfo.classList.add('hidden');
        searchInput.focus();
    }

    // Event listeners
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value;
        
        if (query.length === 0) {
            dropdown.classList.add('hidden');
            clearBtn.classList.add('hidden');
            return;
        }

        clearBtn.classList.remove('hidden');
        const results = searchLibros(query);
        renderResults(results);
        dropdown.classList.remove('hidden');
    });

    searchInput.addEventListener('focus', (e) => {
        if (e.target.value.length > 0) {
            const results = searchLibros(e.target.value);
            renderResults(results);
            dropdown.classList.remove('hidden');
        } else if (!selectedLibro) {
            // Mostrar todos los libros si no hay nada escrito
            renderResults(libros.slice(0, 20)); // Primeros 20
            dropdown.classList.remove('hidden');
        }
    });

    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        dropdown.classList.add('hidden');
        clearBtn.classList.add('hidden');
        searchInput.focus();
    });

    removeBtn.addEventListener('click', clearSelection);

    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', (e) => {
        if (!e.target.closest(`#${nameId}_search`) && 
            !e.target.closest(`#${nameId}_dropdown`)) {
            dropdown.classList.add('hidden');
        }
    });

    // Si hay un libro pre-seleccionado (old value o selected)
    @if(old($name, $selected))
        const preselectedId = {{ old($name, $selected) }};
        const preselected = libros.find(l => l.id == preselectedId);
        if (preselected) {
            selectLibro(preselected);
        }
    @endif
})();
</script>
