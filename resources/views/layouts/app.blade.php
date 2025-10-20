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
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
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
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Sidebar con menú (fijo) -->
        <x-sidebar />

        <!-- Contenido principal (con margen izquierdo para el sidebar) -->
        <div class="ml-64 flex flex-col min-h-screen">
            <!-- Header -->
            <x-header />

            <!-- Contenido -->
            <main class="flex-1 p-6">
                <div class="max-w-7xl mx-auto">
                    @if(session('success'))
                        <x-alert type="success" :message="session('success')" />
                    @endif

                    @if(session('error'))
                        <x-alert type="error" :message="session('error')" />
                    @endif

                    @yield('content')
                </div>
            </main>

            <!-- Footer -->
            <x-footer />
        </div>
    </div>

    <!-- Componentes globales de JavaScript puro -->
    <x-loading />
    <x-confirm-dialog />
</body>
</html>
