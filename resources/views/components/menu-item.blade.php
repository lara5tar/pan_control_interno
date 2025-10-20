@props(['icon', 'label', 'route', 'active' => false])

<a href="{{ is_string($route) && $route === '/' ? $route : route($route) }}" 
   class="flex items-center px-6 py-3 text-white hover:bg-primary-600 transition-colors duration-200 {{ $active ? 'bg-primary-700' : '' }}">
    <i class="{{ $icon }} w-5 mr-3"></i>
    <span class="font-medium">{{ $label }}</span>
</a>
