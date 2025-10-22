@props(['title', 'description' => null, 'buttonText' => null, 'buttonIcon' => null, 'buttonRoute' => null])

<div class="flex justify-between items-center w-full">
    <div class="flex-1">
        <h3 class="text-xl font-semibold text-gray-800">{{ $title }}</h3>
        @if($description)
            <p class="text-gray-600 text-sm mt-1">{{ $description }}</p>
        @endif
    </div>
    
    <div class="flex gap-3 flex-shrink-0">
        @if($buttonText && $buttonRoute)
            <x-button 
                variant="secondary" 
                icon="{{ $buttonIcon ?? 'fas fa-plus' }}"
                onclick="window.location='{{ $buttonRoute }}'"
            >
                {{ $buttonText }}
            </x-button>
        @endif
        
        {{ $slot }}
    </div>
</div>
