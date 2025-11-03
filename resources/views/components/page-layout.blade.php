@props([
    'title',
    'description' => null,
    'buttonText' => null,
    'buttonIcon' => null,
    'buttonRoute' => null,
    'centered' => false
])

<div>
    <!-- Encabezado -->
    <x-page-header 
        :title="$title"
        :description="$description"
        :button-text="$buttonText"
        :button-icon="$buttonIcon"
        :button-route="$buttonRoute"
    >
        {{ $header ?? '' }}
    </x-page-header>

    <!-- Contenido -->
    <div class="mt-6">
        @if($centered)
            <div class="max-w-3xl mx-auto space-y-6">
                {{ $slot }}
            </div>
        @else
            <div class="space-y-6">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
