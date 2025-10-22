@props([
    'title',
    'description' => null,
    'buttonText' => null,
    'buttonIcon' => null,
    'buttonRoute' => null,
    'centered' => false
])

<div class="space-y-6">
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
    @if($centered)
        <div class="max-w-3xl mx-auto space-y-6">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif
</div>
