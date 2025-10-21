<aside class="fixed left-0 top-0 h-screen w-56 bg-gray-50 border-r border-gray-200 text-gray-700 overflow-y-auto">
    <!-- Header -->
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-gray-800 rounded-lg flex items-center justify-center">
                <i class="fas fa-book-open text-sm text-white"></i>
            </div>
            <div>
                <h1 class="text-sm font-semibold text-gray-800">Pan de Vida</h1>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="p-3 space-y-1">
        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            Menu
        </div>
        
        <x-menu-item 
            icon="fas fa-home" 
            label="Dashboard" 
            route="/" 
            :active="request()->is('/')"
        />

        <x-menu-item 
            icon="fas fa-boxes" 
            label="Inventario" 
            route="inventario.index" 
            :active="request()->routeIs('inventario.*')"
        />

        <x-menu-item 
            icon="fas fa-exchange-alt" 
            label="Movimientos" 
            route="movimientos.index" 
            :active="request()->routeIs('movimientos.*')"
        />
    </nav>

    <!-- Footer -->
    <div class="absolute bottom-0 left-0 right-0 p-3 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center gap-3 px-2 py-2">
            <div class="w-8 h-8 bg-gray-800 rounded-full flex items-center justify-center text-white text-xs font-medium">
                UA
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-800 truncate">Usuario Admin</p>
                <p class="text-xs text-gray-500">Administrador</p>
            </div>
        </div>
    </div>
</aside>
