// JavaScript Puro para Control Interno
// Sin frameworks, solo Vanilla JS

// Importar librería de escaneo de código de barras
import { Html5Qrcode } from 'html5-qrcode';

// Hacer disponible globalmente para los componentes Blade
window.Html5Qrcode = Html5Qrcode;

/**
 * Confirmación de eliminación
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // Confirmación para formularios de eliminación
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de eliminar este registro?')) {
                e.preventDefault();
            }
        });
    });

    // Auto-cerrar alertas después de 5 segundos
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Validación de formularios en tiempo real
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], textarea[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateInput(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('border-red-500')) {
                    validateInput(this);
                }
            });
        });
    });

    // Búsqueda en tiempo real (debounced)
    const searchInputs = document.querySelectorAll('input[data-search]');
    searchInputs.forEach(input => {
        let timeout = null;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                // Aquí puedes agregar lógica de búsqueda si lo necesitas
                console.log('Buscando:', this.value);
            }, 300);
        });
    });

    // Toggle de menú móvil
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Confirmación antes de salir con cambios sin guardar
    const formInputs = document.querySelectorAll('form input, form textarea, form select');
    let formChanged = false;
    
    formInputs.forEach(input => {
        input.addEventListener('change', function() {
            formChanged = true;
        });
    });

    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });

    // Resetear flag cuando se envía el formulario
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            formChanged = false;
        });
    });

    // Tooltips simples
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            showTooltip(this);
        });
        
        element.addEventListener('mouseleave', function() {
            hideTooltip(this);
        });
    });

    // Copiar al portapapeles
    const copyButtons = document.querySelectorAll('[data-copy]');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const text = this.getAttribute('data-copy');
            copyToClipboard(text);
            showNotification('Copiado al portapapeles', 'success');
        });
    });

});

/**
 * Validar input individual
 */
function validateInput(input) {
    const value = input.value.trim();
    const type = input.type;
    
    // Remover clases de error previas
    input.classList.remove('border-red-500');
    const errorMsg = input.parentElement.querySelector('.error-message');
    if (errorMsg) errorMsg.remove();
    
    // Validar campo requerido
    if (input.hasAttribute('required') && value === '') {
        showInputError(input, 'Este campo es obligatorio');
        return false;
    }
    
    // Validar email
    if (type === 'email' && value !== '') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showInputError(input, 'Email inválido');
            return false;
        }
    }
    
    // Validar número
    if (type === 'number' && value !== '') {
        const min = input.getAttribute('min');
        const max = input.getAttribute('max');
        
        if (min && parseFloat(value) < parseFloat(min)) {
            showInputError(input, `El valor mínimo es ${min}`);
            return false;
        }
        
        if (max && parseFloat(value) > parseFloat(max)) {
            showInputError(input, `El valor máximo es ${max}`);
            return false;
        }
    }
    
    return true;
}

/**
 * Mostrar error en input
 */
function showInputError(input, message) {
    input.classList.add('border-red-500');
    
    const errorDiv = document.createElement('p');
    errorDiv.className = 'error-message text-red-500 text-sm mt-1';
    errorDiv.textContent = message;
    
    input.parentElement.appendChild(errorDiv);
}

/**
 * Mostrar tooltip
 */
function showTooltip(element) {
    const text = element.getAttribute('data-tooltip');
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip absolute bg-gray-800 text-white text-xs rounded py-1 px-2 z-50';
    tooltip.textContent = text;
    tooltip.id = 'tooltip-' + Date.now();
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
    tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
    
    element.setAttribute('data-tooltip-id', tooltip.id);
}

/**
 * Ocultar tooltip
 */
function hideTooltip(element) {
    const tooltipId = element.getAttribute('data-tooltip-id');
    if (tooltipId) {
        const tooltip = document.getElementById(tooltipId);
        if (tooltip) tooltip.remove();
        element.removeAttribute('data-tooltip-id');
    }
}

/**
 * Copiar texto al portapapeles
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text);
    } else {
        // Fallback para navegadores antiguos
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }
}

/**
 * Mostrar notificación temporal
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        type === 'warning' ? 'bg-yellow-500' :
        'bg-blue-500'
    } text-white`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transition = 'opacity 0.5s';
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

/**
 * Utilidades para tablas
 */
const TableUtils = {
    // Ordenar tabla por columna
    sortTable(tableId, columnIndex, ascending = true) {
        const table = document.getElementById(tableId);
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aValue = a.cells[columnIndex].textContent.trim();
            const bValue = b.cells[columnIndex].textContent.trim();
            
            if (ascending) {
                return aValue.localeCompare(bValue, undefined, { numeric: true });
            } else {
                return bValue.localeCompare(aValue, undefined, { numeric: true });
            }
        });
        
        rows.forEach(row => tbody.appendChild(row));
    },
    
    // Filtrar tabla
    filterTable(tableId, searchTerm) {
        const table = document.getElementById(tableId);
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm.toLowerCase())) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
};

/**
 * Utilidades de formulario
 */
const FormUtils = {
    // Limpiar formulario
    clearForm(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
            // Limpiar errores
            form.querySelectorAll('.border-red-500').forEach(el => {
                el.classList.remove('border-red-500');
            });
            form.querySelectorAll('.error-message').forEach(el => el.remove());
        }
    },
    
    // Deshabilitar formulario
    disableForm(formId) {
        const form = document.getElementById(formId);
        if (form) {
            const inputs = form.querySelectorAll('input, textarea, select, button');
            inputs.forEach(input => input.disabled = true);
        }
    },
    
    // Habilitar formulario
    enableForm(formId) {
        const form = document.getElementById(formId);
        if (form) {
            const inputs = form.querySelectorAll('input, textarea, select, button');
            inputs.forEach(input => input.disabled = false);
        }
    }
};

// Exportar utilidades globalmente
window.TableUtils = TableUtils;
window.FormUtils = FormUtils;
window.showNotification = showNotification;
window.copyToClipboard = copyToClipboard;

