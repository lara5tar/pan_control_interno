@props(['type' => 'button', 'variant' => 'primary', 'icon' => null])

@php
    $classes = [
        'primary' => 'bg-primary-500 hover:bg-primary-600 text-white',
        'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white',
        'success' => 'bg-green-600 hover:bg-green-700 text-white',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white',
        'warning' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
    ];
@endphp

<button 
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center ' . $classes[$variant]]) }}
>
    @if($icon)
        <i class="{{ $icon }} mr-2"></i>
    @endif
    {{ $slot }}
</button>
