<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Control Interno')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Configuración de Tailwind para los colores personalizados
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f9fafb',
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            400: '#9ca3af',
                            500: '#6b7280',
                            600: '#4b5563',
                            700: '#374151',
                            800: '#1f2937',
                            900: '#111827',
                        },
                        accent: {
                            400: '#fcd34d',
                            500: '#fbbf24',
                            600: '#f59e0b',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white overflow-y-scroll">
    <div class="min-h-screen">
        <!-- Sidebar con menú -->
        <x-sidebar />

        <!-- Contenido principal (con margen izquierdo para el sidebar) -->
        <div class="ml-56 flex flex-col min-h-screen">
            <!-- Contenido -->
            <main class="flex-1 p-6 bg-gray-50">
                <div class="max-w-7xl mx-auto">
                    @if(session('success'))
                        <x-alert type="success" :message="session('success')" />
                    @endif

                    @if(session('error'))
                        <x-alert type="error" :message="session('error')" />
                    @endif

                    @if(session('warning'))
                        <x-alert type="warning" :message="session('warning')" />
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Componentes globales de JavaScript puro -->
    <x-loading />
    <x-confirm-dialog />

    @stack('scripts')
</body>
</html>
