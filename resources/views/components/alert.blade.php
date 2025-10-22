@props(['type' => 'info', 'message' => null])

@php
    $classes = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700',
    ];

    $icons = [
        'success' => 'fas fa-check-circle',
        'error' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle',
    ];
@endphp

<div class="border-l-4 p-4 mb-4 rounded {{ $classes[$type] }} alert-dismissible relative" role="alert">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <i class="{{ $icons[$type] }} mr-2"></i>
            <p>{{ $message ?? $slot }}</p>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="text-gray-500 hover:text-gray-700 ml-4">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
