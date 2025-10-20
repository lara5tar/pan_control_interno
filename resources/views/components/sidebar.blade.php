<aside class="w-64 bg-gradient-to-b from-primary-500 to-primary-600 text-white shadow-lg relative min-h-screen">
    <div class="p-6">
        <h1 class="text-2xl font-bold text-center">
            <i class="fas fa-book-open"></i> Control Interno
        </h1>
    </div>

    <nav class="mt-6">
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

        <!-- Aquí se pueden agregar más items del menú -->
    </nav>

    <div class="absolute bottom-0 w-64 p-4 bg-primary-700">
        <div class="text-sm text-sky-100 text-center">
            <i class="fas fa-user-circle"></i> Usuario Admin
        </div>
    </div>
</aside>
