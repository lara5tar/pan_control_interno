@props([
    'viewRoute' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'deleteMessage' => '¿Estás seguro de eliminar este registro?',
])

<td class="px-6 py-4 whitespace-nowrap text-sm">
    <div class="flex justify-end gap-1">
        @if($viewRoute)
            <x-button 
                href="{{ $viewRoute }}" 
                variant="primary" 
                size="sm"
                icon="fas fa-eye"
                title="Ver detalles">
            </x-button>
        @endif
        
        @if($editRoute)
            <x-button 
                href="{{ $editRoute }}" 
                variant="warning" 
                size="sm"
                icon="fas fa-edit"
                title="Editar">
            </x-button>
        @endif
        
        @if($deleteRoute)
            <form action="{{ $deleteRoute }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <x-button 
                    type="submit" 
                    variant="danger" 
                    size="sm"
                    icon="fas fa-trash"
                    title="Eliminar"
                    onclick="return confirm('{{ $deleteMessage }}')">
                </x-button>
            </form>
        @endif

        {{ $slot }}
    </div>
</td>
