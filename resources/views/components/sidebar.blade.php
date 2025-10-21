<aside class="fixed left-0 top-0 h-screen w-64 bg-gradient-to-b from-primary-500 to-primary-600 text-white shadow-lg overflow-y-auto">
    <div class="p-6">
        <h1 class="text-2xl font-bold text-center">
            <i class="fas fa-book-open"></i> Control Interno
        </h1>
    </div>

    <nav class="mt-6 pb-24">
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

        <!-- Aquí se pueden agregar más items del menú -->
    </nav>

    <div class="fixed bottom-0 left-0 w-64 p-4 bg-primary-700">
        <div class="text-sm text-sky-100 text-center">
            <i class="fas fa-user-circle"></i> Usuario Admin
        </div>
    </div>
</aside>
