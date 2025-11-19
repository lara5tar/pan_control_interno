@extends('layouts.app')

@section('title', 'Detalle de Envío')

@section('page-title', 'Detalle de Envío')
@section('page-description', 'Información completa del envío')

@section('content')
<x-page-layout 
    title="Detalle de Envío"
    :description="'Envío #' . $envio->id"
    button-text="Volver a Envíos"
    button-icon="fas fa-arrow-left"
    :button-route="route('envios.index')"
>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información principal -->
        <div class="lg:col-span-2">
            <x-card title="Información del Envío" class="h-full">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">ID de Envío</p>
                        <p class="text-lg font-mono font-bold text-gray-800">#{{ $envio->id }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Estado de Pago</p>
                        <p class="text-lg font-semibold">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $envio->getBadgeColor() }}">
                                <i class="{{ $envio->getIcon() }} mr-1"></i>
                                {{ $envio->getEstadoLabel() }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Número de Guía / Referencia</p>
                        @if($envio->guia)
                            <p class="text-lg font-mono font-bold text-primary-600">
                                <i class="fas fa-barcode mr-2"></i>
                                {{ $envio->guia }}
                            </p>
                        @else
                            <p class="text-lg font-semibold text-gray-400 italic">
                                Sin número de guía
                            </p>
                        @endif
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Fecha de Envío</p>
                        <p class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-calendar-alt text-gray-400 mr-1"></i>
                            {{ $envio->fecha_envio->format('d/m/Y') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Cantidad de Ventas</p>
                        <p class="text-lg font-semibold">
                            <span class="px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                <i class="fas fa-shopping-cart mr-1"></i>
                                {{ $envio->ventas->count() }} venta(s)
                            </span>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total de Libros</p>
                        <p class="text-lg font-semibold">
                            <span class="px-3 py-1 rounded-full text-sm bg-purple-100 text-purple-800">
                                <i class="fas fa-book mr-1"></i>
                                {{ $envio->total_libros }} libro(s)
                            </span>
                        </p>
                    </div>

                    @if($envio->usuario)
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-600 mb-1">Registrado por</p>
                            <p class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-user text-gray-400 mr-1"></i>
                                {{ $envio->usuario }}
                            </p>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

        <!-- Resumen de Costos -->
        <div class="lg:col-span-1">
            <x-card title="Costos del Envío" class="h-full">
                <div class="flex flex-col justify-between h-full">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Total Ventas:</span>
                            <span class="text-gray-800 font-semibold text-lg">
                                ${{ number_format($envio->calcularTotalVentas(), 2) }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center py-3 bg-primary-50 rounded-lg px-3">
                            <span class="text-gray-800 font-bold">Monto a Pagar FedEx:</span>
                            <span class="text-primary-600 font-bold text-2xl">
                                ${{ number_format($envio->monto_a_pagar, 2) }}
                            </span>
                        </div>
                    </div>

                    @if($envio->comprobante)
                        <div class="mt-4">
                            <a href="{{ asset('storage/' . $envio->comprobante) }}" 
                               target="_blank"
                               class="flex items-center justify-center px-4 py-3 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                                <i class="fas fa-file-pdf text-green-600 mr-2 text-xl"></i>
                                <span class="text-green-700 font-medium">Ver Factura</span>
                            </a>
                        </div>
                    @endif

                    @if($envio->comprobante_pago)
                        <div class="mt-4">
                            <a href="{{ asset('storage/' . $envio->comprobante_pago) }}" 
                               target="_blank"
                               class="flex items-center justify-center px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                                <i class="fas fa-receipt text-blue-600 mr-2 text-xl"></i>
                                <span class="text-blue-700 font-medium">Ver Comprobante de Pago</span>
                            </a>
                        </div>
                    @endif

                    @if($envio->referencia_pago)
                        <div class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-400 rounded">
                            <p class="text-xs font-medium text-blue-800 mb-1">
                                <i class="fas fa-hashtag"></i> Referencia de Pago
                            </p>
                            <p class="text-sm text-gray-700 font-mono">{{ $envio->referencia_pago }}</p>
                        </div>
                    @endif

                    @if($envio->fecha_pago)
                        <div class="mt-4 p-3 bg-green-50 border-l-4 border-green-400 rounded">
                            <p class="text-xs font-medium text-green-800 mb-1">
                                <i class="fas fa-calendar-check"></i> Fecha de Pago
                            </p>
                            <p class="text-sm text-gray-700">{{ $envio->fecha_pago->format('d/m/Y') }}</p>
                        </div>
                    @endif

                    @if($envio->notas)
                        <div class="mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                            <p class="text-xs font-medium text-yellow-800 mb-1">
                                <i class="fas fa-sticky-note"></i> Notas
                            </p>
                            <p class="text-sm text-gray-700">{{ $envio->notas }}</p>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>

    <!-- Ventas Asociadas -->
    <x-card title="Ventas Incluidas en el Envío">
        @if($envio->ventas->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID Venta
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Libros
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($envio->ventas as $venta)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $venta->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $venta->fecha_venta->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $venta->cliente?->nombre ?? 'Sin cliente' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="flex flex-col">
                                        <span>{{ $venta->movimientos->count() }} tipo(s)</span>
                                        <span class="text-xs text-gray-400">
                                            {{ $venta->movimientos->sum('cantidad') }} unidades
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    ${{ number_format($venta->total, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <x-button 
                                        variant="info" 
                                        size="sm" 
                                        icon="fas fa-eye"
                                        onclick="window.location='{{ route('ventas.show', $venta) }}'"
                                    >
                                        Ver
                                    </x-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-right text-sm font-bold text-gray-700">
                                Total del Envío:
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-primary-600">
                                ${{ number_format($envio->calcularTotalVentas(), 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-shopping-cart text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-600">No hay ventas asociadas a este envío</p>
            </div>
        @endif
    </x-card>

    <!-- Detalles de Libros -->
    <x-card title="Detalle de Libros en el Envío">
        @php
            $librosAgrupados = collect();
            foreach($envio->ventas as $venta) {
                foreach($venta->movimientos as $movimiento) {
                    $libroId = $movimiento->libro_id;
                    if ($librosAgrupados->has($libroId)) {
                        $librosAgrupados[$libroId]['cantidad'] += $movimiento->cantidad;
                    } else {
                        $librosAgrupados[$libroId] = [
                            'libro' => $movimiento->libro,
                            'cantidad' => $movimiento->cantidad
                        ];
                    }
                }
            }
        @endphp

        @if($librosAgrupados->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Libro
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Código de Barras
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cantidad Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($librosAgrupados as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $item['libro']->nombre }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $item['libro']->isbn }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ $item['cantidad'] }} unidades
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-right text-sm font-bold text-gray-700">
                                Total de Libros:
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-primary-100 text-primary-800">
                                    {{ $envio->total_libros }} unidades
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-book text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-600">No hay libros en este envío</p>
            </div>
        @endif
    </x-card>

    <!-- Botones de acción -->
    <x-card>
        <div class="flex flex-wrap justify-between gap-4">
            <div class="flex gap-3">
                <x-button 
                    variant="warning" 
                    icon="fas fa-edit"
                    onclick="window.location='{{ route('envios.edit', $envio) }}'"
                >
                    Editar Envío
                </x-button>

                @if($envio->estado_pago === 'pendiente')
                    <x-button 
                        variant="success" 
                        icon="fas fa-money-bill-wave"
                        onclick="window.location='{{ route('envios.mostrar-pago', $envio) }}'"
                    >
                        Marcar como Pagado
                    </x-button>
                @else
                    <form action="{{ route('envios.marcar-pendiente', $envio) }}" method="POST" 
                          onsubmit="return confirm('¿Deseas marcar este envío como pendiente de pago?');">
                        @csrf
                        <x-button type="submit" variant="warning" icon="fas fa-clock">
                            Marcar como Pendiente
                        </x-button>
                    </form>
                @endif
            </div>

            <div class="flex gap-3">
                <x-button 
                    variant="secondary" 
                    icon="fas fa-arrow-left"
                    onclick="window.location='{{ route('envios.index') }}'"
                    class="w-full justify-center"
                >
                    Volver al Listado
                </x-button>

                <form action="{{ route('envios.destroy', $envio) }}" method="POST" 
                      onsubmit="return confirm('¿Estás seguro de eliminar este envío?');">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" icon="fas fa-trash">
                        Eliminar
                    </x-button>
                </form>
            </div>
        </div>
    </x-card>
</x-page-layout>
@endsection
