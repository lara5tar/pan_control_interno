<!-- Navbar -->
<nav id="navbar" class="fixed top-0 left-0 right-0 bg-white border-b border-gray-200 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo y nombre -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-gray-800 to-gray-600 rounded-xl flex items-center justify-center shadow-md">
                    <i class="fas fa-dove text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-800">Pan de Vida</h1>
                    <p class="text-xs text-gray-500">Control Interno</p>
                </div>
            </div>

            <!-- Menú Desktop -->
            <div class="hidden md:flex items-center space-x-1">
                <a href="{{ route('dashboard') }}" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-150
                          {{ request()->routeIs('dashboard') 
                              ? 'bg-gray-800 text-white' 
                              : 'text-gray-700 hover:bg-gray-100' }}">
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </a>
                
                <a href="{{ route('inventario.index') }}" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-150
                          {{ request()->routeIs('inventario.*') 
                              ? 'bg-gray-800 text-white' 
                              : 'text-gray-700 hover:bg-gray-100' }}">
                    <i class="fas fa-boxes mr-2"></i>
                    Inventario
                </a>
                
                {{-- TEMPORAL: Módulo de Movimientos oculto --}}
                {{-- <a href="{{ route('movimientos.index') }}" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-150
                          {{ request()->routeIs('movimientos.*') 
                              ? 'bg-gray-800 text-white' 
                              : 'text-gray-700 hover:bg-gray-100' }}">
                    <i class="fas fa-exchange-alt mr-2"></i>
                    Movimientos
                </a> --}}
            </div>

            <!-- Usuario Desktop -->
            <div class="hidden md:flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-800">Usuario Admin</p>
                    <p class="text-xs text-gray-500">Administrador</p>
                </div>
                <div class="w-10 h-10 bg-gradient-to-br from-gray-800 to-gray-600 rounded-full flex items-center justify-center text-white text-sm font-semibold shadow-md">
                    UA
                </div>
            </div>

            <!-- Botón menú móvil -->
            <button 
                id="mobileMenuBtn" 
                class="md:hidden p-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                aria-label="Abrir menú"
            >
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Menú Móvil (desplegable) -->
    <div id="mobileMenu" class="hidden md:hidden border-t border-gray-200 bg-white">
        <div class="px-4 py-3 space-y-1">
            <a href="{{ route('dashboard') }}" 
               class="flex items-center px-4 py-3 rounded-lg text-sm font-medium transition-colors duration-150
                      {{ request()->routeIs('dashboard') 
                          ? 'bg-gray-800 text-white' 
                          : 'text-gray-700 hover:bg-gray-100' }}">
                <i class="fas fa-home w-6"></i>
                <span class="ml-3">Dashboard</span>
            </a>
            
            <a href="{{ route('inventario.index') }}" 
               class="flex items-center px-4 py-3 rounded-lg text-sm font-medium transition-colors duration-150
                      {{ request()->routeIs('inventario.*') 
                          ? 'bg-gray-800 text-white' 
                          : 'text-gray-700 hover:bg-gray-100' }}">
                <i class="fas fa-boxes w-6"></i>
                <span class="ml-3">Inventario</span>
            </a>
            
            {{-- TEMPORAL: Módulo de Movimientos oculto --}}
            {{-- <a href="{{ route('movimientos.index') }}" 
               class="flex items-center px-4 py-3 rounded-lg text-sm font-medium transition-colors duration-150
                      {{ request()->routeIs('movimientos.*') 
                          ? 'bg-gray-800 text-white' 
                          : 'text-gray-700 hover:bg-gray-100' }}">
                <i class="fas fa-exchange-alt w-6"></i>
                <span class="ml-3">Movimientos</span>
            </a> --}}

            <!-- Usuario en móvil -->
            <div class="flex items-center gap-3 px-4 py-3 mt-4 border-t border-gray-200">
                <div class="w-10 h-10 bg-gradient-to-br from-gray-800 to-gray-600 rounded-full flex items-center justify-center text-white text-sm font-semibold shadow-md">
                    UA
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Usuario Admin</p>
                    <p class="text-xs text-gray-500">Administrador</p>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            
            // Cambiar icono
            const icon = this.querySelector('i');
            if (mobileMenu.classList.contains('hidden')) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            } else {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            }
        });

        // Cerrar menú al hacer click en un enlace
        const menuLinks = mobileMenu.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.classList.add('hidden');
                const icon = mobileMenuBtn.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            });
        });

        // Cerrar menú al hacer resize a desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                mobileMenu.classList.add('hidden');
                const icon = mobileMenuBtn.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
});
</script>
