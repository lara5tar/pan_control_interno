@props(['type' => 'button', 'variant' => 'primary', 'icon' => null, 'size' => 'md'])

@php
    $classes = [
        'primary' => 'bg-gray-800 hover:bg-gray-900 text-white',
        'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white',
        'success' => 'bg-green-600 hover:bg-green-700 text-white',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white',
        'warning' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
    ];
    
    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2',
        'lg' => 'px-6 py-3 text-lg',
    ];
@endphp

<button 
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $sizes[$size] . ' rounded-lg font-medium transition-colors duration-200 flex items-center justify-center ' . $classes[$variant]]) }}
>
    @if($icon)
        <i class="{{ $icon }} {{ $slot->isNotEmpty() ? 'max-sm:mr-0 sm:mr-2' : '' }}"></i>
    @endif
    <span class="max-sm:hidden">{{ $slot }}</span>
</button>
