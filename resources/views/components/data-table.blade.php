@props([
    'headers' => [],
    'rows' => [],
    'emptyMessage' => 'No hay datos disponibles',
    'emptyIcon' => 'fas fa-inbox',
    'actions' => true,
    'showActions' => true,
])

<div class="overflow-x-auto">
    @if(count($rows) > 0)
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $header }}
                        </th>
                    @endforeach
                    @if($showActions)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                {{ $slot }}
            </tbody>
        </table>
    @else
        <div class="text-center py-12">
            <i class="{{ $emptyIcon }} text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-500 text-lg mb-4">{{ $emptyMessage }}</p>
        </div>
    @endif
</div>
