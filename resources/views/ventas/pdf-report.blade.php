<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas</title>
    <style>
        {!! $styles !!}
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE VENTAS</h1>
        <p>Generado el {{ date('d/m/Y H:i:s') }}</p>
    </div>

    @if(count($filtros) > 0)
        <div class="filters">
            <h3>Filtros Aplicados:</h3>
            <ul>
                @foreach($filtros as $filtro)
                    <li>{{ $filtro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Resumen de estadísticas --}}
    @if(isset($estadisticas))
        <div class="summary">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="border: none; padding: 5px;">
                        <span class="summary-label">Total Ventas:</span>
                        <span class="summary-value">{{ $estadisticas['total'] ?? 0 }}</span>
                    </td>
                    <td style="border: none; padding: 5px;">
                        <span class="summary-label">Monto Total:</span>
                        <span class="summary-value badge-success">${{ number_format($estadisticas['monto_total'] ?? 0, 2) }}</span>
                    </td>
                    <td style="border: none; padding: 5px;">
                        <span class="summary-label">Unidades Vendidas:</span>
                        <span class="summary-value badge-info">{{ $estadisticas['unidades_vendidas'] ?? 0 }}</span>
                    </td>
                    <td style="border: none; padding: 5px;">
                        <span class="summary-label">Con Envío:</span>
                        <span class="summary-value badge-info">{{ $estadisticas['ventas_con_envio'] ?? 0 }}</span>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    @if($ventas->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th class="text-center">Origen</th>
                    <th class="text-center">Apartado</th>
                    <th class="text-center">Libros</th>
                    <th class="text-center">Unidades</th>
                    <th>Tipo Pago</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Envío</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventas as $venta)
                    <tr>
                        <td>#{{ $venta->id }}</td>
                        <td>{{ $venta->fecha_venta->format('d/m/Y') }}</td>
                        <td>{{ $venta->cliente?->nombre ?: 'Sin cliente' }}</td>
                        <td class="text-center">
                            @if($venta->tipo_inventario === 'subinventario' && $venta->subinventario)
                                <span class="badge badge-info" style="font-size: 9px;">SubInv #{{ $venta->subinventario->id }}</span>
                            @else
                                <span style="font-size: 9px;">General</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($venta->esApartado() && $venta->apartado)
                                <span class="badge badge-warning" style="font-size: 8px;">Apt #{{ $venta->apartado->id }}</span>
                            @else
                                <span style="color: #999; font-size: 9px;">-</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $venta->movimientos->count() }}</td>
                        <td class="text-center">{{ $venta->movimientos->sum('cantidad') }}</td>
                        <td>
                            <span class="badge badge-info">
                                {{ $venta->getTipoPagoLabel() }}
                            </span>
                        </td>
                        <td class="text-right">${{ number_format($venta->total, 2) }}</td>
                        <td class="text-center">
                            @if($venta->tiene_envio)
                                <span class="badge badge-info">Sí</span>
                            @else
                                <span style="color: #999;">No</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $venta->estado === 'completada' ? 'badge-success' : ($venta->estado === 'cancelada' ? 'badge-danger' : 'badge-warning') }}">
                                {{ ucfirst($venta->estado) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>Total de registros: {{ $ventas->count() }}</p>
            <p>Pan de Vida - Sistema de Control Interno</p>
        </div>
    @else
        <div class="empty">
            No hay ventas para mostrar con los filtros aplicados
        </div>
    @endif
</body>
</html>
