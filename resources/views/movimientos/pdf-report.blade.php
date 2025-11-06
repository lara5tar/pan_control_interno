<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Movimientos</title>
    <style>
        {!! $styles !!}
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE MOVIMIENTOS DE INVENTARIO</h1>
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
                        <span class="summary-label">Total Movimientos:</span>
                        <span class="summary-value">{{ $estadisticas['total'] ?? 0 }}</span>
                    </td>
                    <td style="border: none; padding: 5px;">
                        <span class="summary-label">Total Entradas:</span>
                        <span class="summary-value badge-success">{{ $estadisticas['entradas'] ?? 0 }} unidades</span>
                    </td>
                    <td style="border: none; padding: 5px;">
                        <span class="summary-label">Total Salidas:</span>
                        <span class="summary-value badge-danger">{{ $estadisticas['salidas'] ?? 0 }} unidades</span>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    @if($movimientos->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Libro</th>
                    <th>Tipo</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Precio Unit.</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movimientos as $movimiento)
                    <tr>
                        <td>{{ $movimiento->created_at->format('d/m/Y') }}</td>
                        <td>{{ $movimiento->libro->nombre ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $movimiento->tipo_movimiento === 'entrada' ? 'badge-success' : 'badge-danger' }}">
                                {{ $movimiento->getTipoLabel() }}
                            </span>
                        </td>
                        <td class="text-center">{{ $movimiento->cantidad }}</td>
                        <td class="text-right">${{ number_format($movimiento->precio_unitario, 2) }}</td>
                        <td class="text-right">
                            ${{ number_format($movimiento->precio_unitario * $movimiento->cantidad * (1 - ($movimiento->descuento / 100)), 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>Total de registros: {{ $movimientos->count() }}</p>
            <p>Pan de Vida - Sistema de Control Interno</p>
        </div>
    @else
        <div class="empty">
            No hay movimientos para mostrar con los filtros aplicados
        </div>
    @endif
</body>
</html>
