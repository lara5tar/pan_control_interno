/**
 * Apartado Form Manager
 * Gestiona la funcionalidad del formulario de apartados
 */

class ApartadoFormManager {
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
            form: document.getElementById('apartadoForm'),
            
            // Libros
            addLibroBtn: document.getElementById('addLibroBtn'),
            librosContainer: document.getElementById('librosContainer'),
            template: document.getElementById('libroTemplate'),
            descuentoGlobal: document.getElementById('descuento_global'),
            enganche: document.getElementById('enganche'),
            
            // Displays
            subtotalDisplay: document.getElementById('subtotalDisplay'),
            descuentoDisplay: document.getElementById('descuentoDisplay'),
            totalDisplay: document.getElementById('totalDisplay'),
            engancheDisplay: document.getElementById('engancheDisplay'),
            saldoDisplay: document.getElementById('saldoDisplay')
        };
    }

    /**
     * Inicializar todos los módulos
     */
    init() {
        this.initLibros();
        this.initCalculations();
        this.initValidation();
        this.initExistingLibros(); // Inicializar libros que ya existen en el DOM
    }

    /**
     * GESTIÓN DE LIBROS
     */
    initLibros() {
        this.elements.addLibroBtn.addEventListener('click', () => this.addLibro());
        this.elements.librosContainer.addEventListener('click', (e) => this.handleLibroClick(e));
    }

    /**
     * Inicializar componentes de búsqueda para libros que ya existen en el DOM
     * (por ejemplo, cuando hay errores de validación y se preservan los datos con old())
     */
    initExistingLibros() {
        console.log('[Apartado Form] Initializing existing libro items...');
        
        // Usar requestAnimationFrame para asegurar que el DOM esté completamente renderizado
        requestAnimationFrame(() => {
            const existingLibroItems = document.querySelectorAll('.libro-item');
            
            if (existingLibroItems.length === 0) {
                console.log('[Apartado Form] No existing libro items found');
                return;
            }
            
            console.log(`[Apartado Form] Found ${existingLibroItems.length} existing libro items`);
            
            existingLibroItems.forEach((item, index) => {
                const searchContainer = item.querySelector('[id*="libro_search_libros_"]');
                if (searchContainer) {
                    const containerId = searchContainer.id;
                    console.log(`[Apartado Form] Initializing libro search for existing item ${index}:`, containerId);
                    
                    if (window.apartadoLibrosData && typeof window.initLibroSearch === 'function') {
                        window.libroSearchInstances[containerId] = window.initLibroSearch(
                            containerId,
                            window.apartadoLibrosData
                        );
                    }
                }
            });
            
            // Eliminar mensaje de vacío si hay libros
            if (existingLibroItems.length > 0) {
                this.removeEmptyMessage();
            }
            
            // Actualizar botones inline después de inicializar
            this.updateAddLibroButtons();
            
            // Recalcular totales
            this.calculateTotal();
        });
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
        
        if (window.apartadoLibrosData && typeof window.initLibroSearch === 'function') {
            console.log('[Apartado Form] Initializing libro search for:', searchContainerId);
            window.libroSearchInstances[searchContainerId] = window.initLibroSearch(
                searchContainerId, 
                window.apartadoLibrosData
            );
        } else {
            console.error('[Apartado Form] Cannot initialize libro search - missing data or function');
        }
        
        this.libroIndex++;
        
        this.removeEmptyMessage();
        this.updateAddLibroButtons();
        this.updateCalculations();
    }

    handleLibroClick(e) {
        if (e.target.closest('.remove-libro')) {
            e.target.closest('.libro-item').remove();
            this.reindexLibros();
            this.showEmptyMessageIfNeeded();
            this.updateAddLibroButtons();
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
    
    /**
     * Actualizar visibilidad de botones "Agregar Otro Libro"
     * Solo el último libro debe mostrar el botón
     */
    updateAddLibroButtons() {
        const libroItems = document.querySelectorAll('.libro-item');
        
        // Ocultar todos los botones primero
        libroItems.forEach(item => {
            const container = item.querySelector('.add-libro-inline-container');
            if (container) {
                container.classList.add('hidden');
            }
        });
        
        // Mostrar solo en el último libro
        if (libroItems.length > 0) {
            const lastItem = libroItems[libroItems.length - 1];
            const lastContainer = lastItem.querySelector('.add-libro-inline-container');
            if (lastContainer) {
                lastContainer.classList.remove('hidden');
            }
        }
    }

    removeEmptyMessage() {
        const msg = document.getElementById('emptyMessage');
        if (msg) msg.remove();
    }

    showEmptyMessageIfNeeded() {
        if (this.elements.librosContainer.children.length === 0) {
            this.elements.librosContainer.innerHTML = `
                <div id="emptyMessage" class="text-center py-12 text-gray-500 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                    <i class="fas fa-book text-4xl mb-3"></i>
                    <p>No hay libros agregados. Haz clic en "Agregar Libro" para empezar.</p>
                </div>`;
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
        this.elements.enganche.addEventListener('input', () => this.updateCalculations());
        
        this.updateCalculations();
    }

    updateCalculations() {
        this.calculateTotal();
    }

    calculateTotal() {
        let subtotal = 0;

        // Calcular subtotal de todos los libros
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
                
                // Validar stock disponible para apartado
                if (stockMessage) {
                    if (cantidad > stock) {
                        stockMessage.textContent = `⚠️ Stock insuficiente (disponible: ${stock})`;
                        stockMessage.className = 'stock-message text-xs text-red-600 mt-1';
                    } else {
                        stockMessage.textContent = `✓ Stock disponible: ${stock}`;
                        stockMessage.className = 'stock-message text-xs text-green-600 mt-1';
                    }
                }
                
                // Calcular subtotal del libro
                const precioConDescuento = precio - (precio * descuento / 100);
                const subtotalItem = precioConDescuento * cantidad;
                if (subtotalLibro) {
                    subtotalLibro.textContent = '$' + subtotalItem.toFixed(2);
                }
                subtotal += subtotalItem;
            }
        });

        // Aplicar descuento global
        const descuentoGlobalValor = parseFloat(this.elements.descuentoGlobal.value) || 0;
        const descuentoMonto = subtotal * descuentoGlobalValor / 100;
        const total = subtotal - descuentoMonto;

        // Calcular saldo pendiente
        const enganche = parseFloat(this.elements.enganche.value) || 0;
        const saldo = Math.max(0, total - enganche);

        // Actualizar displays
        this.elements.subtotalDisplay.textContent = '$' + subtotal.toFixed(2);
        this.elements.descuentoDisplay.textContent = '-$' + descuentoMonto.toFixed(2);
        this.elements.totalDisplay.textContent = '$' + total.toFixed(2);
        this.elements.engancheDisplay.textContent = '$' + enganche.toFixed(2);
        this.elements.saldoDisplay.textContent = '$' + saldo.toFixed(2);
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
            alert('Debes agregar al menos un libro al apartado.');
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

        // Validar que el enganche no sea mayor al total
        const total = parseFloat(this.elements.totalDisplay.textContent.replace('$', '').replace(',', '')) || 0;
        const enganche = parseFloat(this.elements.enganche.value) || 0;

        if (enganche > total) {
            alert('El enganche no puede ser mayor al total del apartado.');
            this.elements.enganche.focus();
            return false;
        }

        return true;
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('apartadoForm');
    if (form) {
        const libroIndex = parseInt(form.dataset.libroIndex) || 0;
        window.apartadoFormManagerInstance = new ApartadoFormManager(libroIndex);
    }
});
