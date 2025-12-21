@extends('layouts.app')

@section('title', 'Detalle del Apartado')

@section('content')
<x-page-layout 
    title="Detalle del Apartado"
    description="Apartado {{ $apartado->folio }}"
    button-text="Volver a Apartados"
    button-icon="fas fa-arrow-left"
    :button-route="route('apartados.index')"
>
    <!-- Información Principal -->
    <div class="grid grid-cols-3 gap-6 mb-6">
        <!-- Información del Apartado -->
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-info-circle mr-2"></i> Información del Apartado
            </h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">ID / Folio</p>
                    <p class="font-medium">#{{ $apartado->id }} / {{ $apartado->folio }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Estado</p>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $apartado->getBadgeColor() }}">
                        <i class="{{ $apartado->getIcon() }} mr-1"></i>
                        {{ $apartado->getEstadoLabel() }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Cliente</p>
                    <p class="font-medium">
                        <i class="fas fa-user mr-1"></i> {{ $apartado->cliente->nombre }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Fecha de Apartado</p>
                    <p class="font-medium">{{ $apartado->fecha_apartado->format('d/m/Y') }}</p>
                </div>
                @if($apartado->fecha_limite)
                <div>
                    <p class="text-sm text-gray-600">Fecha Límite</p>
                    <p class="font-medium {{ $apartado->estaVencido ? 'text-red-600' : '' }}">
                        {{ $apartado->fecha_limite->format('d/m/Y') }}
                        @if($apartado->estaVencido)
                            <span class="text-xs">(Vencido)</span>
                        @endif
                    </p>
                </div>
                @endif
            </div>
        </x-card>

        <!-- Resumen de Totales -->
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-calculator mr-2"></i> Resumen de Totales
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Monto Total:</span>
                    <span class="text-xl font-bold text-gray-900">${{ number_format($apartado->monto_total, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Pagado:</span>
                    <span class="text-xl font-bold text-green-600">${{ number_format($apartado->totalPagado, 2) }}</span>
                </div>
                <div class="flex justify-between items-center border-t pt-3">
                    <span class="text-gray-600 font-semibold">Saldo Pendiente:</span>
                    <span class="text-2xl font-bold text-orange-600">${{ number_format($apartado->saldo_pendiente, 2) }}</span>
                </div>
                <div class="bg-blue-50 p-3 rounded">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-blue-800">Porcentaje Pagado:</span>
                        <span class="text-lg font-bold text-blue-900">{{ $apartado->porcentajePagado }}%</span>
                    </div>
                    <div class="w-full bg-blue-200 rounded-full h-2 mt-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $apartado->porcentajePagado }}%"></div>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Acciones -->
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-tasks mr-2"></i> Acciones
            </h3>
            <div class="space-y-3">
                @if($apartado->estado === 'activo')
                    @if($apartado->saldo_pendiente > 0)
                        <a href="{{ route('apartados.abonos.create', $apartado) }}" class="btn btn-primary w-full">
                            <i class="fas fa-dollar-sign mr-2"></i> Registrar Abono
                        </a>
                    @else
                        <form action="{{ route('apartados.liquidar', $apartado) }}" method="POST" onsubmit="return confirm('¿Liquidar este apartado y crear la venta?')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success w-full">
                                <i class="fas fa-check-circle mr-2"></i> Liquidar Apartado
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('apartados.cancelar', $apartado) }}" method="POST" onsubmit="return confirm('¿Está seguro de cancelar este apartado?')">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-danger w-full">
                            <i class="fas fa-times-circle mr-2"></i> Cancelar Apartado
                        </button>
                    </form>
                @endif

                @if($apartado->estado === 'liquidado' && $apartado->venta_id)
                    <a href="{{ route('ventas.show', $apartado->venta_id) }}" class="btn btn-info w-full">
                        <i class="fas fa-receipt mr-2"></i> Ver Venta Generada
                    </a>
                @endif

                <a href="{{ route('apartados.index') }}" class="btn btn-secondary w-full">
                    <i class="fas fa-list mr-2"></i> Volver al Listado
                </a>
            </div>
        </x-card>
    </div>

    <!-- Historial de Abonos -->
    <x-card class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-history mr-2"></i> Historial de Abonos ({{ $apartado->abonos->count() }})
            </h3>
            @if($apartado->estado === 'activo' && $apartado->saldo_pendiente > 0)
                <a href="{{ route('apartados.abonos.create', $apartado) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus mr-1"></i> Registrar Nuevo Abono
                </a>
            @endif
        </div>

        @if($apartado->abonos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saldo Anterior</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saldo Nuevo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($apartado->abonos()->latest()->get() as $abono)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                ${{ number_format($abono->monto, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $abono->fecha_abono->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 bg-gray-100 rounded">
                                    <i class="fas fa-{{ $abono->metodo_pago === 'efectivo' ? 'money-bill' : ($abono->metodo_pago === 'tarjeta' ? 'credit-card' : 'exchange-alt') }} mr-1"></i>
                                    {{ $abono->getMetodoPagoLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                ${{ number_format($abono->saldo_anterior, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                ${{ number_format($abono->saldo_nuevo, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <i class="fas fa-user mr-1"></i> {{ $abono->usuario }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($loop->first && $apartado->estado === 'activo')
                                    <form action="{{ route('apartados.abonos.destroy', $abono) }}" method="POST" onsubmit="return confirm('¿Eliminar este abono?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <p>No hay abonos registrados aún</p>
            </div>
        @endif
    </x-card>

    <!-- Libros Apartados -->
    <x-card>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-book mr-2"></i> Libros Apartados ({{ $apartado->detalles->count() }})
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($apartado->detalles as $detalle)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">{{ $detalle->libro->nombre }}</h4>
                            <p class="text-sm text-gray-600">Código: {{ $detalle->libro->codigo }}</p>
                        </div>
                        <a href="{{ route('inventario.show', $detalle->libro) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-gray-600">Precio Unitario</p>
                            <p class="font-medium">${{ number_format($detalle->precio_unitario, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Cantidad</p>
                            <p class="font-medium">{{ $detalle->cantidad }} unidad(es)</p>
                        </div>
                        @if($detalle->descuento > 0)
                        <div>
                            <p class="text-gray-600">Descuento</p>
                            <p class="font-medium text-green-600">{{ $detalle->descuento }}%</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-gray-600">Subtotal</p>
                            <p class="font-bold text-gray-900">${{ number_format($detalle->subtotal, 2) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>

    <!-- Información de Registro -->
    @if($apartado->observaciones)
    <x-card class="mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">
            <i class="fas fa-comment-alt mr-2"></i> Observaciones
        </h3>
        <p class="text-gray-700">{{ $apartado->observaciones }}</p>
    </x-card>
    @endif
</x-page-layout>
@endsection
