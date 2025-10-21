@props(['type' => 'success'])

@php
    $colors = [
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'danger' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800',
        'primary' => 'bg-primary-100 text-primary-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'px-3 py-1 rounded-full text-xs font-medium inline-flex items-center ' . $colors[$type]]) }}>
    {{ $slot }}
</span>
