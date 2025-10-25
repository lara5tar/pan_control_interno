@props([
    'bold' => false,
    'center' => false,
])

@php
    $classes = 'px-6 py-4 whitespace-nowrap text-sm';
    if ($bold) $classes .= ' font-medium text-gray-900';
    else $classes .= ' text-gray-500';
    if ($center) $classes .= ' text-center';
@endphp

<td {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</td>
