<div id="loading-spinner" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white p-5 rounded-lg shadow-lg">
        <div class="flex items-center space-x-3">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
            <span class="text-gray-700 font-medium" id="loading-message">Cargando...</span>
        </div>
    </div>
</div>

<script>
const loadingSpinner = {
    show(message = 'Cargando...') {
        const spinner = document.getElementById('loading-spinner');
        const messageEl = document.getElementById('loading-message');
        
        messageEl.textContent = message;
        spinner.classList.remove('hidden');
    },
    
    hide() {
        const spinner = document.getElementById('loading-spinner');
        spinner.classList.add('hidden');
    }
};

// Hacer global
window.loadingSpinner = loadingSpinner;

// Auto-mostrar en envío de formularios
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            // Solo mostrar si no es un formulario de búsqueda
            if (!this.hasAttribute('data-no-loading')) {
                loadingSpinner.show('Procesando...');
            }
        });
    });
});
</script>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
