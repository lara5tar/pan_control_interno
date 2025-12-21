@extends('layouts.app')

@section('title', 'Apartados')

@section('content')
<x-page-layout 
    title="Listado de Apartados"
    description="Total: {{ $apartados->total() }} apartados"
>
    <x-slot name="header">
        <x-button 
            variant="primary" 
            icon="fas fa-plus"
            onclick="window.location='{{ route('apartados.create') }}'"
        >
            Nuevo Apartado
        </x-button>
    </x-slot>

    <!-- Estadísticas -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
        <x-stat-card 
            icon="fas fa-handshake"
            label="Total Apartados"
            :value="$estadisticas['total_apartados']"
            bg-color="bg-blue-100"
            icon-color="text-blue-600"
        />

        <x-stat-card 
            icon="fas fa-clock"
            label="Activos"
            :value="$estadisticas['activos']"
            bg-color="bg-yellow-100"
            icon-color="text-yellow-600"
        />

        <x-stat-card 
            icon="fas fa-check-circle"
            label="Liquidados"
            :value="$estadisticas['liquidados']"
            bg-color="bg-green-100"
            icon-color="text-green-600"
        />

        <x-stat-card 
            icon="fas fa-dollar-sign"
            label="Total Apartado"
            :value="'$' . number_format($estadisticas['total_apartado'], 2)"
            bg-color="bg-purple-100"
            icon-color="text-purple-600"
        />

        <x-stat-card 
            icon="fas fa-exclamation-circle"
            label="Saldo Pendiente"
            :value="'$' . number_format($estadisticas['saldo_pendiente_total'], 2)"
            bg-color="bg-orange-100"
            icon-color="text-orange-600"
        />

        @if($estadisticas['vencidos'] > 0)
        <x-stat-card 
            icon="fas fa-hourglass-end"
            label="Vencidos"
            :value="$estadisticas['vencidos']"
            bg-color="bg-red-100"
            icon-color="text-red-600"
        />
        @endif
    </div>

    <!-- Filtros -->
    <x-card>
        <form method="GET" action="{{ route('apartados.index') }}">
            <div class="grid grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                    <select name="cliente_id" class="form-select w-full">
                        <option value="">Todos los clientes</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado" class="form-select w-full">
                        <option value="">Todos los estados</option>
                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="liquidado" {{ request('estado') == 'liquidado' ? 'selected' : '' }}>Liquidado</option>
                        <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                    <input type="date" name="fecha_desde" class="form-input w-full" value="{{ request('fecha_desde') }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-input w-full" value="{{ request('fecha_hasta') }}">
                </div>
            </div>

            <div class="flex justify-between items-center">
                <x-button type="submit" variant="primary" icon="fas fa-filter">
                    Aplicar Filtros
                </x-button>
                <a href="{{ route('apartados.index') }}" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times-circle mr-1"></i> Limpiar Filtros
                </a>
            </div>
        </form>
    </x-card>

    <!-- Tabla de Apartados -->
    <x-card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID / Folio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha / Límite</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pagado / Saldo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($apartados as $apartado)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">#{{ $apartado->id }}</div>
                            <div class="text-xs text-gray-500">{{ $apartado->folio }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $apartado->fecha_apartado->format('d/m/Y') }}</div>
                            @if($apartado->fecha_limite)
                                <div class="text-xs {{ $apartado->estaVencido ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                    <i class="fas fa-clock mr-1"></i>Vence: {{ $apartado->fecha_limite->format('d/m/Y') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $apartado->cliente->nombre }}</div>
                            <div class="text-xs text-gray-500">
                                <i class="fas fa-book mr-1"></i>{{ $apartado->detalles->count() }} libro(s)
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${{ number_format($apartado->monto_total, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-green-600 font-medium">
                                <i class="fas fa-check mr-1"></i>${{ number_format($apartado->totalPagado, 2) }}
                            </div>
                            <div class="text-sm text-orange-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>${{ number_format($apartado->saldo_pendiente, 2) }}
                            </div>
                            <div class="text-xs text-gray-500">{{ $apartado->porcentajePagado }}% pagado</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $apartado->getBadgeColor() }}">
                                <i class="{{ $apartado->getIcon() }} mr-1"></i>
                                {{ $apartado->getEstadoLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex gap-2">
                                <a href="{{ route('apartados.show', $apartado) }}" class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($apartado->estado === 'activo')
                                    <a href="{{ route('apartados.abonos.create', $apartado) }}" class="text-green-600 hover:text-green-900" title="Registrar abono">
                                        <i class="fas fa-dollar-sign"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No se encontraron apartados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="mt-4">
            {{ $apartados->links() }}
        </div>
    </x-card>
</x-page-layout>
@endsection
