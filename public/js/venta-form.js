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
            

            
            // Displays
            subtotalDisplay: document.getElementById('subtotalDisplay'),
            descuentoDisplay: document.getElementById('descuentoDisplay'),
            costoEnvioDisplay: document.getElementById('costoEnvioDisplay'),
            costoEnvioDisplayContainer: document.getElementById('costoEnvioDisplayContainer'),
            totalDisplay: document.getElementById('totalDisplay'),
            
            // Campos de envío
            tieneEnvio: document.getElementById('tiene_envio'),
            costoEnvio: document.getElementById('costo_envio')
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
        this.restoreFormState();
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
        console.log('[Venta Form] Initializing existing libro items...');
        
        // Usar requestAnimationFrame para asegurar que el DOM esté completamente renderizado
        requestAnimationFrame(() => {
            // Solo seleccionar libros EDITABLES (excluir libros eliminados con clase libro-eliminado-readonly)
            const existingLibroItems = document.querySelectorAll('.libro-item:not(.libro-eliminado-readonly)');
            
            if (existingLibroItems.length === 0) {
                console.log('[Venta Form] No existing editable libro items found');
                return;
            }
            
            console.log(`[Venta Form] Found ${existingLibroItems.length} existing editable libro items`);
            
            existingLibroItems.forEach((item, index) => {
                const searchContainer = item.querySelector('[id*="libro_search_libros_"]');
                if (searchContainer) {
                    const containerId = searchContainer.id;
                    console.log(`[Venta Form] Initializing libro search for existing item ${index}:`, containerId);
                    
                    if (window.ventaLibrosData && typeof window.initLibroSearch === 'function') {
                        window.libroSearchInstances[containerId] = window.initLibroSearch(
                            containerId,
                            window.ventaLibrosData
                        );
                    }
                }
            });
            
            // Eliminar mensaje de vacío si hay libros (editables o eliminados)
            const allLibroItems = document.querySelectorAll('.libro-item, .libro-eliminado-readonly');
            if (allLibroItems.length > 0) {
                this.removeEmptyMessage();
            }
            
            // Actualizar botones inline después de inicializar
            this.updateAddLibroButtons();
            
            // Recalcular totales para libros eliminados
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
        this.updateAddLibroButtons(); // Actualizar botones inline
        this.updateCalculations();
    }

    handleLibroClick(e) {
        if (e.target.closest('.remove-libro')) {
            e.target.closest('.libro-item').remove();
            this.reindexLibros();
            this.showEmptyMessageIfNeeded();
            this.updateAddLibroButtons(); // Actualizar botones inline después de eliminar
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
                <div id="emptyMessage" class="text-center py-8 text-gray-500">
                    <i class="fas fa-book text-4xl mb-3"></i>
                    <p>No hay libros agregados. Haz clic en "Agregar Libro" para empezar.</p>
                </div>`;
        }
    }

    /**
     * Guardar estado del formulario en sessionStorage
     */
    saveFormState() {
        const clienteIdInput = document.querySelector('input[name="cliente_id"]');
        
        const formData = {
            clienteId: clienteIdInput?.value || '',
            libros: [],
            descuentoGlobal: this.elements.descuentoGlobal.value,
            fechaVenta: document.querySelector('input[name="fecha_venta"]')?.value,
            tipoPago: document.querySelector('select[name="tipo_pago"]')?.value,
            observaciones: document.querySelector('textarea[name="observaciones"]')?.value
        };

        // Guardar cada libro agregado
        document.querySelectorAll('.libro-item').forEach((item, index) => {
            const libroIdInput = item.querySelector('input[name*="[libro_id]"]');
            const cantidadInput = item.querySelector('.cantidad-input');
            const descuentoInput = item.querySelector('.descuento-input');
            
            if (libroIdInput && libroIdInput.value) {
                formData.libros.push({
                    libro_id: libroIdInput.value,
                    libro_nombre: libroIdInput.getAttribute('data-nombre'),
                    libro_precio: libroIdInput.getAttribute('data-precio'),
                    libro_stock: libroIdInput.getAttribute('data-stock'),
                    cantidad: cantidadInput.value,
                    descuento: descuentoInput.value
                });
            }
        });

        sessionStorage.setItem('ventaFormState', JSON.stringify(formData));
        sessionStorage.setItem('ventaFormReturnUrl', window.location.href);
        console.log('Estado del formulario guardado:', formData);
    }

    /**
     * Restaurar estado del formulario desde sessionStorage
     */
    restoreFormState() {
        const savedState = sessionStorage.getItem('ventaFormState');
        const returnUrl = sessionStorage.getItem('ventaFormReturnUrl');
        
        // Solo restaurar si estamos en la misma URL
        if (!savedState || window.location.href !== returnUrl) {
            return;
        }

        try {
            const formData = JSON.parse(savedState);
            console.log('Restaurando estado del formulario:', formData);

            // Restaurar cliente
            if (formData.clienteId) {
                const clienteIdInput = document.querySelector('input[name="cliente_id"]');
                if (clienteIdInput) {
                    clienteIdInput.value = formData.clienteId;
                }
            }

            // Restaurar campos generales
            if (formData.descuentoGlobal) {
                this.elements.descuentoGlobal.value = formData.descuentoGlobal;
            }
            
            if (formData.fechaVenta) {
                const fechaInput = document.querySelector('input[name="fecha_venta"]');
                if (fechaInput) fechaInput.value = formData.fechaVenta;
            }
            
            if (formData.tipoPago) {
                const tipoPagoSelect = document.querySelector('select[name="tipo_pago"]');
                if (tipoPagoSelect) tipoPagoSelect.value = formData.tipoPago;
            }
            
            if (formData.observaciones) {
                const observacionesTextarea = document.querySelector('textarea[name="observaciones"]');
                if (observacionesTextarea) observacionesTextarea.value = formData.observaciones;
            }

            // Restaurar libros
            if (formData.libros && formData.libros.length > 0) {
                // Preservar libros eliminados (elementos con bg-red-50) - NO eliminarlos
                const deletedBooks = Array.from(this.elements.librosContainer.querySelectorAll('.libro-item.bg-red-50'));
                console.log('[Venta Form] Preserving', deletedBooks.length, 'deleted books');
                
                // NO limpiar si hay libros eliminados que deben preservarse
                if (deletedBooks.length === 0) {
                    // Solo limpiar si no hay libros eliminados
                    this.elements.librosContainer.innerHTML = '';
                }
                
                this.libroIndex = deletedBooks.length; // Empezar desde el índice correcto

                formData.libros.forEach((libro, index) => {
                    // Agregar libro después de los eliminados
                    const newLibro = this.elements.template.content.cloneNode(true);
                    const libroDiv = newLibro.querySelector('.libro-item');
                    
                    libroDiv.innerHTML = libroDiv.innerHTML.replace(/INDEX_PLACEHOLDER/g, index);
                    libroDiv.dataset.index = index;
                    libroDiv.querySelector('.libro-number').textContent = index + 1;
                    
                    this.elements.librosContainer.appendChild(newLibro);
                    
                    // Establecer valores del libro
                    setTimeout(() => {
                        const libroItem = document.querySelector(`.libro-item[data-index="${index}"]`);
                        if (libroItem) {
                            const libroIdInput = libroItem.querySelector('input[name*="[libro_id]"]');
                            const cantidadInput = libroItem.querySelector('.cantidad-input');
                            const descuentoInput = libroItem.querySelector('.descuento-input');
                            const libroNombre = libroItem.querySelector('.libro-nombre');
                            
                            if (libroIdInput) {
                                libroIdInput.value = libro.libro_id;
                                libroIdInput.setAttribute('data-nombre', libro.libro_nombre);
                                libroIdInput.setAttribute('data-precio', libro.libro_precio);
                                libroIdInput.setAttribute('data-stock', libro.libro_stock);
                            }
                            
                            if (libroNombre) {
                                libroNombre.textContent = libro.libro_nombre;
                                libroNombre.classList.remove('hidden');
                            }
                            
                            if (cantidadInput) cantidadInput.value = libro.cantidad;
                            if (descuentoInput) descuentoInput.value = libro.descuento;
                            
                            // Ocultar el buscador de libro
                            const searchContainer = libroItem.querySelector('[id*="_container"]');
                            if (searchContainer) {
                                const searchInput = searchContainer.querySelector('input[type="text"]');
                                if (searchInput) searchInput.classList.add('hidden');
                            }
                        }
                    }, 100);
                    
                    this.libroIndex++;
                });

                setTimeout(() => {
                    this.updateCalculations();
                }, 200);
            }

            // Limpiar sessionStorage después de restaurar
            sessionStorage.removeItem('ventaFormState');
            sessionStorage.removeItem('ventaFormReturnUrl');

            // Mostrar notificación
            if (window.showNotification) {
                window.showNotification('Formulario restaurado correctamente', 'success');
            }
        } catch (error) {
            console.error('Error al restaurar el estado del formulario:', error);
            sessionStorage.removeItem('ventaFormState');
            sessionStorage.removeItem('ventaFormReturnUrl');
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
        
        // Escuchar cambios en el campo de costo de envío
        if (this.elements.costoEnvio) {
            this.elements.costoEnvio.addEventListener('input', () => this.updateCalculations());
        }
        
        // Escuchar cambios en el checkbox de tiene_envio
        if (this.elements.tieneEnvio) {
            this.elements.tieneEnvio.addEventListener('change', () => this.updateCalculations());
        }
        
        this.updateCalculations();
    }

    updateCalculations() {
        let subtotal = 0;

        // Incluir tanto libros editables como eliminados (readonly)
        document.querySelectorAll('.libro-item, .libro-eliminado-readonly').forEach(item => {
            const libroIdInput = item.querySelector('input[name*="[libro_id]"]');
            const cantidadInput = item.querySelector('.cantidad-input');
            const descuentoInput = item.querySelector('.descuento-input');
            const subtotalLibro = item.querySelector('.subtotal-libro');
            const stockMessage = item.querySelector('.stock-message');
            
            if (libroIdInput && libroIdInput.value) {
                // Para libros eliminados, obtener el precio del input hidden precio_unitario
                let precio;
                if (item.classList.contains('libro-eliminado-readonly')) {
                    const precioUnitarioInput = item.querySelector('input[name*="[precio_unitario]"]');
                    precio = precioUnitarioInput ? parseFloat(precioUnitarioInput.value) || 0 : 0;
                } else {
                    // Para libros normales, obtener precio y stock de los atributos data del input
                    precio = parseFloat(libroIdInput.getAttribute('data-precio')) || 0;
                    const stock = parseInt(libroIdInput.getAttribute('data-stock')) || 0;
                    const cantidad = parseInt(cantidadInput.value) || 0;
                    
                    // Validar stock (solo para libros editables)
                    if (stockMessage) {
                        if (cantidad > stock) {
                            stockMessage.textContent = `⚠️ Stock insuficiente (disponible: ${stock})`;
                            stockMessage.className = 'stock-message text-xs text-red-600 mt-1';
                        } else {
                            stockMessage.textContent = `✓ Stock restante: ${stock - cantidad}`;
                            stockMessage.className = 'stock-message text-xs text-green-600 mt-1';
                        }
                    }
                }
                
                const cantidad = parseInt(cantidadInput.value) || 0;
                const descuento = parseFloat(descuentoInput.value) || 0;
                
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
        let total = subtotal - descuentoMonto;
        
        // Agregar costo de envío si está marcado
        const tieneEnvio = this.elements.tieneEnvio && this.elements.tieneEnvio.checked;
        const costoEnvio = tieneEnvio ? (parseFloat(this.elements.costoEnvio.value) || 0) : 0;
        
        if (costoEnvio > 0) {
            total += costoEnvio;
            this.elements.costoEnvioDisplay.textContent = '+$' + costoEnvio.toFixed(2);
            this.elements.costoEnvioDisplayContainer.style.display = 'flex';
        } else {
            this.elements.costoEnvioDisplayContainer.style.display = 'none';
        }

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
    window.ventaFormManagerInstance = new VentaFormManager(libroIndex);
});
