@extends('layouts.app')

@section('title', 'Detalle de Venta')

@section('page-title', 'Detalle de Venta')
@section('page-description', 'Información completa de la venta')

@section('content')
<x-page-layout 
    title="Detalle de Venta"
    :description="'Venta #' . $venta->id"
    button-text="Volver a Ventas"
    button-icon="fas fa-arrow-left"
    :button-route="route('ventas.index')"
>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información principal -->
        <div class="lg:col-span-2">
            <x-card title="Información de la Venta" class="h-full">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">ID de Venta</p>
                        <p class="text-lg font-mono font-bold text-gray-800">#{{ $venta->id }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Estado</p>
                        <p class="text-lg font-semibold">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $venta->getEstadoUnificadoBadgeColor() }}">
                                <i class="{{ $venta->getEstadoUnificadoIcon() }} mr-1"></i>
                                {{ $venta->getEstadoUnificadoLabel() }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Cliente</p>
                        @if($venta->cliente)
                            <p class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-user text-primary-600 mr-2"></i>
                                {{ $venta->cliente->nombre }}
                            </p>
                            @if($venta->cliente->telefono)
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-phone mr-1"></i>
                                    {{ $venta->cliente->telefono }}
                                </p>
                            @endif
                        @else
                            <p class="text-lg font-semibold text-gray-400 italic">
                                Sin cliente asignado
                            </p>
                        @endif
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Fecha de Venta</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $venta->fecha_venta->format('d/m/Y') }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tipo de Pago</p>
                        <p class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-credit-card text-gray-400 mr-1"></i>
                            {{ $venta->getTipoPagoLabel() }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Cantidad de Libros</p>
                        <p class="text-lg font-semibold">
                            <span class="px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                {{ $venta->movimientos->count() }} libro(s)
                            </span>
                        </p>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Resumen de Totales -->
        <div class="lg:col-span-1">
            <x-card title="Resumen de Totales" class="h-full">
                <div class="flex flex-col justify-between h-full">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Subtotal:</span>
                            <span class="text-gray-800 font-semibold text-lg">
                                ${{ number_format($venta->subtotal, 2) }}
                            </span>
                        </div>

                        @if($venta->descuento_global > 0)
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="text-orange-600 font-medium">
                                    Descuento ({{ $venta->descuento_global }}%):
                                </span>
                                <span class="text-orange-600 font-semibold text-lg">
                                    -${{ number_format(($venta->subtotal * $venta->descuento_global / 100), 2) }}
                                </span>
                            </div>
                        @endif

                        <div class="flex justify-between items-center py-3 bg-primary-50 rounded-lg px-3">
                            <span class="text-gray-800 font-bold">Total:</span>
                            <span class="text-primary-600 font-bold text-2xl">
                                ${{ number_format($venta->total, 2) }}
                            </span>
                        </div>
                    </div>

                    @if($venta->observaciones)
                        <div class="mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                            <p class="text-xs font-medium text-yellow-800 mb-1">
                                <i class="fas fa-sticky-note"></i> Observaciones
                            </p>
                            <p class="text-sm text-gray-700">{{ $venta->observaciones }}</p>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>

    <!-- Gestión de Pagos -->
    @if($venta->es_a_plazos)
        <div id="pagos"></div>
        <x-card title="Gestión de Pagos a Plazos">
            <!-- Resumen de Estado de Pago -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="text-center p-4 bg-green-50 rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-600 mb-2">Total Pagado</p>
                    <p class="text-2xl font-bold text-green-600">
                        ${{ number_format($venta->total_pagado, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        de ${{ number_format($venta->total, 2) }}
                    </p>
                </div>

                <div class="text-center p-4 bg-orange-50 rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-600 mb-2">Saldo Pendiente</p>
                    <p class="text-2xl font-bold text-orange-600">
                        ${{ number_format($venta->saldo_pendiente, 2) }}
                    </p>
                    <div class="mt-2 bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-green-500 h-2 transition-all duration-300" 
                             style="width: {{ ($venta->total_pagado / $venta->total) * 100 }}%">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ number_format(($venta->total_pagado / $venta->total) * 100, 1) }}% pagado
                    </p>
                </div>

                @if($venta->fecha_limite)
                    <div class="text-center p-4 {{ $venta->fecha_limite->isPast() && $venta->estado_pago !== 'completado' ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-200' }} rounded-lg border">
                        <p class="text-sm text-gray-600 mb-2">Fecha Límite</p>
                        <p class="text-xl font-bold {{ $venta->fecha_limite->isPast() && $venta->estado_pago !== 'completado' ? 'text-red-600' : 'text-blue-600' }}">
                            {{ $venta->fecha_limite->format('d/m/Y') }}
                        </p>
                        @if($venta->fecha_limite->isPast() && $venta->estado_pago !== 'completado')
                            <p class="text-xs text-red-600 mt-1 font-medium">
                                <i class="fas fa-exclamation-triangle"></i> Vencida
                            </p>
                        @else
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $venta->fecha_limite->diffForHumans() }}
                            </p>
                        @endif
                    </div>
                @else
                    <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-600 mb-2">Pagos Realizados</p>
                        <p class="text-2xl font-bold text-gray-700">
                            {{ $venta->pagos->count() }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            pago(s) registrado(s)
                        </p>
                    </div>
                @endif
            </div>

            @if($venta->estado_pago === 'completado')
                <!-- Mensaje de Completado -->
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg mb-6">
                    <p class="text-sm text-green-800 text-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>¡Venta completamente pagada!</strong> El stock ya ha sido descontado.
                    </p>
                </div>
            @endif

            <!-- Historial de Pagos -->
            @if($venta->pagos->count() > 0)
                <div class="mt-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-3 gap-3">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-history text-gray-500 mr-2"></i>
                            Historial de Pagos ({{ $venta->pagos->count() }})
                        </h3>
                        @if($venta->estado_pago !== 'completado' && $venta->estado !== 'cancelada')
                            <x-button 
                                href="{{ route('ventas.pagos.create', $venta) }}" 
                                variant="primary" 
                                icon="fas fa-plus-circle">
                                Registrar Nuevo Pago
                            </x-button>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Pago</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comprobante</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrado</th>
                                    @if($venta->estado_pago !== 'completado' && $venta->estado !== 'cancelada')
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($venta->pagos->sortByDesc('fecha_pago') as $index => $pago)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gradient-to-br from-green-100 to-green-200 rounded-full text-green-700 font-bold text-sm">
                                                {{ $venta->pagos->count() - $index }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="text-lg font-bold text-green-600">
                                                ${{ number_format($pago->monto, 2) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pago->metodo_pago === 'contado' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                                <i class="fas {{ $pago->metodo_pago === 'contado' ? 'fa-money-bill-wave' : 'fa-credit-card' }} mr-1"></i>
                                                {{ $pago->getTipoPagoLabel() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                            {{ $pago->comprobante ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900 max-w-xs">
                                            @if($pago->notas)
                                                <div class="truncate" title="{{ $pago->notas }}">
                                                    <i class="fas fa-sticky-note text-blue-500 mr-1"></i>
                                                    {{ $pago->notas }}
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <i class="fas fa-clock text-gray-400 mr-1"></i>
                                            {{ $pago->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        @if($venta->estado_pago !== 'completado' && $venta->estado !== 'cancelada')
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                <form action="{{ route('pagos.destroy', $pago) }}" method="POST" 
                                                      onsubmit="return confirm('¿Estás seguro de eliminar este pago?')"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors"
                                                            title="Eliminar pago">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <!-- Sin pagos aún -->
                <div class="flex flex-col items-center justify-center py-6">
                    <i class="fas fa-receipt text-gray-300 text-5xl mb-3"></i>
                    <p class="text-gray-500 text-base font-medium mb-1">
                        Aún no se han registrado pagos para esta venta
                    </p>
                    <p class="text-gray-400 text-sm mb-5">
                        Registra el primer abono o pago para comenzar
                    </p>
                    @if($venta->estado_pago !== 'completado' && $venta->estado !== 'cancelada')
                        <x-button 
                            href="{{ route('ventas.pagos.create', $venta) }}" 
                            variant="primary" 
                            icon="fas fa-plus-circle">
                            Registrar Primer Pago
                        </x-button>
                    @endif
                </div>
            @endif
        </x-card>
    @else
        <!-- Venta al contado -->
        <x-card title="Información de Pago">
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-center">
                <i class="fas fa-check-circle text-green-600 text-3xl mb-2"></i>
                <p class="text-sm text-green-800">
                    <strong>Venta pagada al contado.</strong>
                </p>
                <p class="text-xs text-green-700 mt-1">
                    El stock ya ha sido descontado del inventario.
                </p>
            </div>
        </x-card>
    @endif

    <!-- Libros de la Venta -->
    <x-card title="Libros de la Venta">
        <div class="space-y-4">
            @foreach($venta->movimientos as $movimiento)
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start gap-4">
                        <!-- Icono del libro -->
                        <div class="flex-shrink-0">
                            <div class="w-16 h-20 bg-gradient-to-br from-primary-100 to-primary-200 rounded-lg flex items-center justify-center">
                                <i class="fas fa-book text-primary-600 text-2xl"></i>
                            </div>
                        </div>

                        <!-- Información del libro -->
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    @if($movimiento->libro)
                                        <h3 class="font-semibold text-gray-900 text-base">
                                            {{ $movimiento->libro->nombre }}
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            Código: <span class="font-mono">{{ $movimiento->libro->codigo_barras }}</span>
                                        </p>
                                    @else
                                        <h3 class="font-semibold text-red-600 text-base">
                                            (Libro eliminado)
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            ID del libro: {{ $movimiento->libro_id }}
                                        </p>
                                    @endif
                                </div>
                                @if($movimiento->libro)
                                    <a href="{{ route('inventario.show', $movimiento->libro) }}" 
                                       class="text-primary-600 hover:text-primary-700"
                                       title="Ver libro">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <!-- Precio Unitario -->
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Precio Unitario</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        ${{ number_format($movimiento->precio_unitario, 2) }}
                                    </p>
                                </div>

                                <!-- Cantidad -->
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Cantidad</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $movimiento->cantidad }} unidad(es)
                                    </p>
                                </div>

                                <!-- Descuento -->
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Descuento</p>
                                    <p class="text-sm font-semibold {{ $movimiento->descuento > 0 ? 'text-orange-600' : 'text-gray-900' }}">
                                        {{ $movimiento->descuento > 0 ? $movimiento->descuento . '%' : '0%' }}
                                    </p>
                                </div>

                                <!-- Subtotal -->
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Subtotal</p>
                                    <p class="text-sm font-bold text-primary-600">
                                        @php
                                            $precioConDescuento = $movimiento->precio_unitario;
                                            if ($movimiento->descuento) {
                                                $precioConDescuento -= ($movimiento->precio_unitario * $movimiento->descuento / 100);
                                            }
                                            $subtotal = $precioConDescuento * $movimiento->cantidad;
                                        @endphp
                                        ${{ number_format($subtotal, 2) }}
                                    </p>
                                </div>
                            </div>

                            @if($movimiento->descuento > 0)
                                <div class="mt-2 text-xs text-orange-600 bg-orange-50 px-2 py-1 rounded inline-block">
                                    <i class="fas fa-tag"></i>
                                    Precio con descuento: ${{ number_format($precioConDescuento, 2) }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>

    <!-- Información de fechas y acciones -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="Información de Registro">
            <div class="space-y-4">
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">
                        <i class="fas fa-user-circle text-blue-500 mr-2"></i>
                        Registrado por
                    </span>
                    <span class="text-gray-800 font-semibold">
                        {{ $venta->usuario ?? 'N/A' }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <span class="text-gray-600 font-medium">
                        <i class="fas fa-calendar-plus text-green-500 mr-2"></i>
                        Fecha de Registro
                    </span>
                    <span class="text-gray-800 font-semibold">
                        {{ $venta->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-gray-600 font-medium">
                        <i class="fas fa-clock text-orange-500 mr-2"></i>
                        Última Actualización
                    </span>
                    <span class="text-gray-800 font-semibold">
                        {{ $venta->updated_at->format('d/m/Y H:i') }}
                    </span>
                </div>
            </div>
        </x-card>

        <!-- Acciones -->
        <x-card title="Acciones">
            <div class="space-y-3">
                @php
                    $roles = session('roles', []);
                    $isAdmin = false;
                    foreach ($roles as $rol) {
                        $rolNombre = strtoupper(trim($rol['ROL'] ?? ''));
                        if ($rolNombre === 'ADMIN LIBRERIA' || $rolNombre === 'ADMIN LIBRERÍA') {
                            $isAdmin = true;
                            break;
                        }
                    }
                @endphp
                
                @if($isAdmin)
                    <x-button 
                        href="{{ route('ventas.edit', $venta) }}" 
                        variant="warning" 
                        icon="fas fa-edit"
                        class="w-full justify-center">
                        Editar Venta
                    </x-button>
                @endif
                
                @if($venta->estado === 'completada')
                    <form action="{{ route('ventas.cancelar', $venta) }}" method="POST">
                        @csrf
                        <x-button 
                            type="submit" 
                            variant="warning" 
                            icon="fas fa-ban"
                            onclick="return confirm('¿Estás seguro de cancelar esta venta? Se restaurará el stock de los libros.')"
                            class="w-full justify-center">
                            Cancelar Venta
                        </x-button>
                    </form>
                @endif
                
                <x-button variant="secondary" icon="fas fa-arrow-left" onclick="window.location='{{ route('ventas.index') }}'" class="w-full justify-center">
                    Volver al Listado
                </x-button>
                
                @if($venta->estado !== 'completada')
                    <form action="{{ route('ventas.destroy', $venta) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger" icon="fas fa-trash" onclick="return confirm('¿Estás seguro de eliminar esta venta?')" class="w-full justify-center">
                            Eliminar
                        </x-button>
                    </form>
                @endif
            </div>
        </x-card>
    </div>
</x-page-layout>
@endsection
