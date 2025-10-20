<header class="bg-white shadow-md">
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800">
                @yield('page-title', 'Panel de Control')
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                @yield('page-description', 'Gestión de inventario y control interno')
            </p>
        </div>
        <div class="flex items-center space-x-4">
            <button class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-bell text-xl"></i>
            </button>
            <button class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-cog text-xl"></i>
            </button>
        </div>
    </div>
</header>
