@props(['title' => null])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg border border-gray-200']) }}>
    @if($title)
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
        </div>
    @endif
    
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
