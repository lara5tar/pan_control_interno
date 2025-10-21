@props(['icon', 'label', 'route', 'active' => false])

<a href="{{ is_string($route) && $route === '/' ? $route : route($route) }}" 
   class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors duration-150 text-sm
          {{ $active 
              ? 'bg-gray-800 text-white font-medium' 
              : 'text-gray-700 hover:bg-gray-100' }}">
    <i class="{{ $icon }} w-4 text-center"></i>
    <span>{{ $label }}</span>
</a>
