@props([
    'viewRoute' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'deleteMessage' => '¿Estás seguro de eliminar este registro?',
])

<td class="px-6 py-4 whitespace-nowrap text-sm">
    <div class="flex items-center space-x-3">
        @if($viewRoute)
            <a href="{{ $viewRoute }}" 
               class="text-gray-800 hover:text-gray-900 transition-colors"
               title="Ver detalles">
                <i class="fas fa-eye"></i>
            </a>
        @endif
        
        @if($editRoute)
            <a href="{{ $editRoute }}" 
               class="text-gray-800 hover:text-gray-900 transition-colors"
               title="Editar">
                <i class="fas fa-edit"></i>
            </a>
        @endif
        
        @if($deleteRoute)
            <form action="{{ $deleteRoute }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="text-red-600 hover:text-red-900 transition-colors p-1" 
                        onclick="return confirm('{{ $deleteMessage }}')"
                        title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endif

        {{ $slot }}
    </div>
</td>
