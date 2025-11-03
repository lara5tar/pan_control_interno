/**
 * Venta Form Manager
 * Gestiona la funcionalidad del formulario de ventas
 */

class VentaFormManager {
    constructor(libroIndexInicial) {
        this.libroIndex = libroIndexInicial;
        this.elements = this.getElements();
        this.init();
    }

    /**
     * Obtener todos los elementos del DOM
     */
    getElements() {
        return {
            // Form
            form: document.getElementById('ventaForm'),
            
            // Libros
            addLibroBtn: document.getElementById('addLibroBtn'),
            librosContainer: document.getElementById('librosContainer'),
            template: document.getElementById('libroTemplate'),
            descuentoGlobal: document.getElementById('descuento_global'),
            
            // Cliente
            clienteSearch: document.getElementById('cliente_search'),
            clienteResults: document.getElementById('clienteResults'),
            clienteId: document.getElementById('cliente_id'),
            clienteSelected: document.getElementById('clienteSelected'),
            clienteNombre: document.getElementById('clienteNombre'),
            clienteTelefono: document.getElementById('clienteTelefono'),
            clearCliente: document.getElementById('clearCliente'),
            
            // Modal Cliente
            btnNuevoCliente: document.getElementById('btnNuevoCliente'),
            modalNuevoCliente: document.getElementById('modalNuevoCliente'),
            closeModal: document.getElementById('closeModal'),
            cancelModal: document.getElementById('cancelModal'),
            guardarCliente: document.getElementById('guardarCliente'),
            nuevoClienteNombre: document.getElementById('nuevo_cliente_nombre'),
            nuevoClienteTelefono: document.getElementById('nuevo_cliente_telefono'),
            
            // Displays
            subtotalDisplay: document.getElementById('subtotalDisplay'),
            descuentoDisplay: document.getElementById('descuentoDisplay'),
            totalDisplay: document.getElementById('totalDisplay')
        };
    }

    /**
     * Inicializar todos los módulos
     */
    init() {
        this.initLibros();
        this.initCliente();
        this.initCalculations();
        this.initValidation();
    }

    /**
     * GESTIÓN DE LIBROS
     */
    initLibros() {
        this.elements.addLibroBtn.addEventListener('click', () => this.addLibro());
        this.elements.librosContainer.addEventListener('click', (e) => this.handleLibroClick(e));
    }

    addLibro() {
        const newLibro = this.elements.template.content.cloneNode(true);
        const libroDiv = newLibro.querySelector('.libro-item');
        
        libroDiv.innerHTML = libroDiv.innerHTML.replace(/INDEX_PLACEHOLDER/g, this.libroIndex);
        libroDiv.dataset.index = this.libroIndex;
        libroDiv.querySelector('.libro-number').textContent = this.libroIndex + 1;
        
        this.elements.librosContainer.appendChild(newLibro);
        
        // Initialize libro search for this new item
        const searchContainerId = `libro_search_libros_${this.libroIndex}_libro_id_container`;
        
        if (window.ventaLibrosData && typeof window.initLibroSearch === 'function') {
            console.log('[Venta Form] Initializing libro search for:', searchContainerId);
            window.libroSearchInstances[searchContainerId] = window.initLibroSearch(
                searchContainerId, 
                window.ventaLibrosData
            );
        } else {
            console.error('[Venta Form] Cannot initialize libro search - missing data or function');
        }
        
        this.libroIndex++;
        
        this.removeEmptyMessage();
        this.updateCalculations();
    }

    handleLibroClick(e) {
        if (e.target.closest('.remove-libro')) {
            e.target.closest('.libro-item').remove();
            this.reindexLibros();
            this.showEmptyMessageIfNeeded();
            this.updateCalculations();
        }
    }

    reindexLibros() {
        document.querySelectorAll('.libro-item').forEach((item, index) => {
            item.querySelector('.libro-number').textContent = index + 1;
            item.dataset.index = index;
            item.querySelectorAll('[name*="libros["]').forEach(input => {
                input.name = input.name.replace(/libros\[\d+\]/, `libros[${index}]`);
            });
        });
        this.libroIndex = this.elements.librosContainer.children.length;
    }

    removeEmptyMessage() {
        const msg = document.getElementById('emptyMessage');
        if (msg) msg.remove();
    }

    showEmptyMessageIfNeeded() {
        if (this.elements.librosContainer.children.length === 0) {
            this.elements.librosContainer.innerHTML = `
                <div id="emptyMessage" class="text-center py-8 text-gray-500">
                    <i class="fas fa-book text-4xl mb-3"></i>
                    <p>No hay libros agregados. Haz clic en "Agregar Libro" para empezar.</p>
                </div>`;
        }
    }

    /**
     * GESTIÓN DE CLIENTE
     */
    initCliente() {
        let searchTimeout;
        
        this.elements.clienteSearch.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                this.elements.clienteResults.classList.add('hidden');
                return;
            }
            
            searchTimeout = setTimeout(() => this.searchClientes(query), 300);
        });

        this.elements.clienteResults.addEventListener('click', (e) => this.selectCliente(e));
        this.elements.clearCliente.addEventListener('click', () => this.clearCliente());
        this.elements.btnNuevoCliente.addEventListener('click', () => this.openModal());
        this.elements.closeModal.addEventListener('click', () => this.closeModal());
        this.elements.cancelModal.addEventListener('click', () => this.closeModal());
        this.elements.guardarCliente.addEventListener('click', () => this.saveCliente());
        
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#clienteSearchContainer')) {
                this.elements.clienteResults.classList.add('hidden');
            }
        });
    }

    async searchClientes(query) {
        try {
            const response = await fetch(`/clientes/search?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            this.elements.clienteResults.innerHTML = data.length === 0
                ? '<div class="p-3 text-gray-500 text-sm">No se encontraron clientes</div>'
                : data.map(c => `
                    <div class="p-3 hover:bg-gray-50 cursor-pointer cliente-item border-b last:border-b-0" 
                         data-id="${c.id}" data-nombre="${c.nombre}" data-telefono="${c.telefono || ''}">
                        <div class="font-semibold text-gray-800">${c.nombre}</div>
                        <div class="text-sm text-gray-600">${c.telefono || 'Sin teléfono'}</div>
                    </div>`).join('');
            
            this.elements.clienteResults.classList.remove('hidden');
        } catch (error) {
            console.error('Error al buscar clientes:', error);
        }
    }

    selectCliente(e) {
        const item = e.target.closest('.cliente-item');
        if (!item) return;
        
        this.elements.clienteId.value = item.dataset.id;
        this.elements.clienteNombre.textContent = item.dataset.nombre;
        this.elements.clienteTelefono.textContent = item.dataset.telefono || 'Sin teléfono';
        this.elements.clienteSelected.classList.remove('hidden');
        this.elements.clienteSearch.value = '';
        this.elements.clienteResults.classList.add('hidden');
    }

    clearCliente() {
        this.elements.clienteId.value = '';
        this.elements.clienteSelected.classList.add('hidden');
        this.elements.clienteSearch.value = '';
    }

    openModal() {
        this.elements.modalNuevoCliente.classList.remove('hidden');
        this.elements.nuevoClienteNombre.focus();
    }

    closeModal() {
        this.elements.modalNuevoCliente.classList.add('hidden');
        this.elements.nuevoClienteNombre.value = '';
        this.elements.nuevoClienteTelefono.value = '';
    }

    async saveCliente() {
        const nombre = this.elements.nuevoClienteNombre.value.trim();
        
        if (!nombre) {
            alert('El nombre del cliente es obligatorio');
            return;
        }

        try {
            const response = await fetch('/clientes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    nombre: nombre,
                    telefono: this.elements.nuevoClienteTelefono.value.trim() || null
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.elements.clienteId.value = result.cliente.id;
                this.elements.clienteNombre.textContent = result.cliente.nombre;
                this.elements.clienteTelefono.textContent = result.cliente.telefono || 'Sin teléfono';
                this.elements.clienteSelected.classList.remove('hidden');
                this.closeModal();
                alert('Cliente creado exitosamente');
            } else {
                alert('Error al crear el cliente');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al crear el cliente');
        }
    }

    /**
     * GESTIÓN DE CÁLCULOS
     */
    initCalculations() {
        this.elements.librosContainer.addEventListener('change', (e) => {
            if (e.target.matches('input[name*="[libro_id]"], .cantidad-input, .descuento-input')) {
                this.updateCalculations();
            }
        });

        this.elements.librosContainer.addEventListener('input', (e) => {
            if (e.target.matches('.cantidad-input, .descuento-input')) {
                this.updateCalculations();
            }
        });

        this.elements.descuentoGlobal.addEventListener('input', () => this.updateCalculations());
        
        this.updateCalculations();
    }

    updateCalculations() {
        let subtotal = 0;

        document.querySelectorAll('.libro-item').forEach(item => {
            const libroIdInput = item.querySelector('input[name*="[libro_id]"]');
            const cantidadInput = item.querySelector('.cantidad-input');
            const descuentoInput = item.querySelector('.descuento-input');
            const subtotalLibro = item.querySelector('.subtotal-libro');
            const stockMessage = item.querySelector('.stock-message');
            
            if (libroIdInput && libroIdInput.value) {
                // Obtener precio y stock de los atributos data del input
                const precio = parseFloat(libroIdInput.getAttribute('data-precio')) || 0;
                const stock = parseInt(libroIdInput.getAttribute('data-stock')) || 0;
                const cantidad = parseInt(cantidadInput.value) || 0;
                const descuento = parseFloat(descuentoInput.value) || 0;
                
                // Validar stock
                if (stockMessage) {
                    if (cantidad > stock) {
                        stockMessage.textContent = `⚠️ Stock insuficiente (disponible: ${stock})`;
                        stockMessage.className = 'stock-message text-xs text-red-600 mt-1';
                    } else {
                        stockMessage.textContent = `✓ Stock restante: ${stock - cantidad}`;
                        stockMessage.className = 'stock-message text-xs text-green-600 mt-1';
                    }
                }
                
                // Calcular subtotal
                const precioConDescuento = precio - (precio * descuento / 100);
                const subtotalItem = precioConDescuento * cantidad;
                if (subtotalLibro) {
                    subtotalLibro.textContent = '$' + subtotalItem.toFixed(2);
                }
                subtotal += subtotalItem;
            }
        });

        // Calcular total
        const descuentoGlobalValor = parseFloat(this.elements.descuentoGlobal.value) || 0;
        const descuentoMonto = subtotal * descuentoGlobalValor / 100;
        const total = subtotal - descuentoMonto;

        // Actualizar displays
        this.elements.subtotalDisplay.textContent = '$' + subtotal.toFixed(2);
        this.elements.descuentoDisplay.textContent = '-$' + descuentoMonto.toFixed(2);
        this.elements.totalDisplay.textContent = '$' + total.toFixed(2);
    }

    /**
     * VALIDACIÓN
     */
    initValidation() {
        this.elements.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
            }
        });
    }

    validateForm() {
        const libros = document.querySelectorAll('.libro-item');
        
        if (libros.length === 0) {
            alert('Debes agregar al menos un libro a la venta.');
            return false;
        }

        let stockValido = true;
        
        libros.forEach(item => {
            const libroIdInput = item.querySelector('input[name*="[libro_id]"]');
            const cantidadInput = item.querySelector('.cantidad-input');
            
            if (libroIdInput && libroIdInput.value) {
                const stock = parseInt(libroIdInput.getAttribute('data-stock')) || 0;
                const cantidad = parseInt(cantidadInput.value) || 0;
                
                if (cantidad > stock) stockValido = false;
            }
        });

        if (!stockValido) {
            alert('Hay libros con stock insuficiente. Por favor, verifica las cantidades.');
            return false;
        }

        return true;
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    const libroIndex = parseInt(document.getElementById('ventaForm').dataset.libroIndex) || 0;
    window.ventaFormManager = new VentaFormManager(libroIndex);
});
