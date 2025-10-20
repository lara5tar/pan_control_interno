<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejemplos JavaScript Puro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto space-y-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Ejemplos de JavaScript Puro</h1>

        <!-- Notificaciones -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">1. Notificaciones Toast</h2>
            <div class="space-x-2">
                <button onclick="showNotification('Operación exitosa', 'success')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    Éxito
                </button>
                <button onclick="showNotification('Ha ocurrido un error', 'error')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                    Error
                </button>
                <button onclick="showNotification('Ten cuidado', 'warning')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                    Advertencia
                </button>
                <button onclick="showNotification('Información útil', 'info')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Info
                </button>
            </div>
        </div>

        <!-- Confirm Dialog -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">2. Dialog de Confirmación</h2>
            <button onclick="testConfirmDialog()" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded">
                Abrir Confirmación
            </button>
        </div>

        <!-- Loading Spinner -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">3. Loading Spinner</h2>
            <button onclick="testLoading()" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded">
                Mostrar Loading (3 segundos)
            </button>
        </div>

        <!-- Modal -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">4. Modal</h2>
            <button onclick="openModal('exampleModal')" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded">
                Abrir Modal
            </button>
        </div>

        <!-- Copiar al portapapeles -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">5. Copiar al Portapapeles</h2>
            <div class="flex items-center gap-2">
                <input type="text" value="https://ejemplo.com/codigo-secreto" readonly class="flex-1 px-4 py-2 border rounded">
                <button data-copy="https://ejemplo.com/codigo-secreto" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded">
                    <i class="fas fa-copy"></i> Copiar
                </button>
            </div>
        </div>

        <!-- Tooltips -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">6. Tooltips</h2>
            <p>
                Pasa el mouse sobre 
                <span data-tooltip="¡Este es un tooltip!" class="text-primary-500 cursor-help underline">
                    este texto
                </span> 
                para ver el tooltip.
            </p>
        </div>

        <!-- Validación de formulario -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">7. Validación en Tiempo Real</h2>
            <form data-validate class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email (requerido)</label>
                    <input type="email" required class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Edad (mínimo 18)</label>
                    <input type="number" min="18" required class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-primary-500">
                </div>
                <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded">
                    Enviar
                </button>
            </form>
        </div>

        <!-- Tabla con utilidades -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">8. Utilidades de Tabla</h2>
            <div class="mb-4">
                <input 
                    type="text" 
                    placeholder="Filtrar tabla..." 
                    onkeyup="TableUtils.filterTable('demoTable', this.value)"
                    class="px-4 py-2 border rounded w-full"
                >
            </div>
            <table id="demoTable" class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left cursor-pointer hover:bg-gray-100" onclick="TableUtils.sortTable('demoTable', 0)">
                            Nombre <i class="fas fa-sort text-xs"></i>
                        </th>
                        <th class="px-4 py-2 text-left cursor-pointer hover:bg-gray-100" onclick="TableUtils.sortTable('demoTable', 1)">
                            Edad <i class="fas fa-sort text-xs"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td class="px-4 py-2 border-t">Juan</td><td class="px-4 py-2 border-t">25</td></tr>
                    <tr><td class="px-4 py-2 border-t">María</td><td class="px-4 py-2 border-t">30</td></tr>
                    <tr><td class="px-4 py-2 border-t">Pedro</td><td class="px-4 py-2 border-t">22</td></tr>
                    <tr><td class="px-4 py-2 border-t">Ana</td><td class="px-4 py-2 border-t">28</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Componentes -->
    <x-loading />
    <x-confirm-dialog />
    
    <x-modal id="exampleModal" title="Modal de Ejemplo">
        <p class="mb-4">Este es un modal completamente funcional con JavaScript puro.</p>
        <p class="mb-4">Puedes cerrarlo haciendo clic fuera, presionando ESC, o en el botón X.</p>
        <button onclick="closeModal('exampleModal')" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded">
            Cerrar
        </button>
    </x-modal>

    <script>
        function testConfirmDialog() {
            confirmDialog.show('¿Estás seguro de realizar esta acción?', (confirmed) => {
                if (confirmed) {
                    showNotification('¡Confirmado!', 'success');
                } else {
                    showNotification('Cancelado', 'info');
                }
            });
        }

        function testLoading() {
            loadingSpinner.show('Procesando datos...');
            setTimeout(() => {
                loadingSpinner.hide();
                showNotification('Proceso completado', 'success');
            }, 3000);
        }
    </script>
</body>
</html>
