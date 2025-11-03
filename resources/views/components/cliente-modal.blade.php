{{--
    Componente: Cliente Modal
    Descripción: Modal para crear un nuevo cliente
--}}

<div id="modalNuevoCliente" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-user-plus text-primary-600 mr-2"></i>
                Nuevo Cliente
            </h3>
            <button type="button" id="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Form -->
        <div class="space-y-4">
            <div>
                <label for="nuevo_cliente_nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nuevo_cliente_nombre" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    required>
            </div>
            
            <div>
                <label for="nuevo_cliente_telefono" class="block text-sm font-medium text-gray-700 mb-2">
                    Teléfono <span class="text-gray-400 text-xs">(Opcional)</span>
                </label>
                <input 
                    type="text" 
                    id="nuevo_cliente_telefono" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex gap-3 mt-6">
            <button type="button" id="cancelModal" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </button>
            <button type="button" id="guardarCliente" class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-save mr-2"></i>
                Guardar
            </button>
        </div>
    </div>
</div>
