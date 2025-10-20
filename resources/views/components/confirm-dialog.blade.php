@props(['message' => '¿Estás seguro?', 'confirmText' => 'Confirmar', 'cancelText' => 'Cancelar'])

<div id="confirm-dialog" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <!-- Icon -->
        <div class="text-center mb-4">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-5xl"></i>
        </div>
        
        <!-- Message -->
        <div class="text-center mb-6">
            <p class="text-lg text-gray-700" id="confirm-message">{{ $message }}</p>
        </div>
        
        <!-- Buttons -->
        <div class="flex gap-3 justify-center">
            <button onclick="confirmDialog.cancel()" class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors">
                {{ $cancelText }}
            </button>
            <button onclick="confirmDialog.confirm()" class="px-6 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition-colors">
                {{ $confirmText }}
            </button>
        </div>
    </div>
</div>

<script>
const confirmDialog = {
    callback: null,
    
    show(message, callback) {
        this.callback = callback;
        const dialog = document.getElementById('confirm-dialog');
        const messageEl = document.getElementById('confirm-message');
        
        if (message) messageEl.textContent = message;
        dialog.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    },
    
    confirm() {
        const dialog = document.getElementById('confirm-dialog');
        dialog.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        if (this.callback) {
            this.callback(true);
            this.callback = null;
        }
    },
    
    cancel() {
        const dialog = document.getElementById('confirm-dialog');
        dialog.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        if (this.callback) {
            this.callback(false);
            this.callback = null;
        }
    }
};

// Hacer global
window.confirmDialog = confirmDialog;

// Cerrar con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const dialog = document.getElementById('confirm-dialog');
        if (!dialog.classList.contains('hidden')) {
            confirmDialog.cancel();
        }
    }
});
</script>
