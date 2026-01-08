<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Apartados</title>
    <style>
        {!! $styles !!}
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE APARTADOS</h1>
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

    @if($apartados->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Folio</th>
                    <th>Cliente</th>
                    <th>Fecha Apartado</th>
                    <th>Fecha Límite</th>
                    <th class="text-right">Monto Total</th>
                    <th class="text-right">Total Pagado</th>
                    <th class="text-right">Saldo Pendiente</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalMonto = 0;
                    $totalPagado = 0;
                    $totalSaldo = 0;
                @endphp
                @foreach($apartados as $apartado)
                    @php
                        $totalMonto += $apartado->monto_total;
                        $totalPagado += $apartado->totalPagado;
                        $totalSaldo += $apartado->saldo_pendiente;
                    @endphp
                    <tr>
                        <td>{{ $apartado->id }}</td>
                        <td>{{ $apartado->folio }}</td>
                        <td>{{ $apartado->cliente->nombre }}</td>
                        <td>{{ $apartado->fecha_apartado->format('d/m/Y') }}</td>
                        <td>{{ $apartado->fecha_limite ? $apartado->fecha_limite->format('d/m/Y') : 'N/A' }}</td>
                        <td class="text-right">${{ number_format($apartado->monto_total, 2) }}</td>
                        <td class="text-right">${{ number_format($apartado->totalPagado, 2) }}</td>
                        <td class="text-right">${{ number_format($apartado->saldo_pendiente, 2) }}</td>
                        <td class="text-center">
                            <span class="badge {{ $apartado->estado === 'activo' ? 'badge-warning' : ($apartado->estado === 'liquidado' ? 'badge-success' : 'badge-danger') }}">
                                {{ $apartado->getEstadoLabel() }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f3f4f6;">
                    <td colspan="5" style="text-align: right;">TOTALES:</td>
                    <td class="text-right">${{ number_format($totalMonto, 2) }}</td>
                    <td class="text-right">${{ number_format($totalPagado, 2) }}</td>
                    <td class="text-right">${{ number_format($totalSaldo, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>Total de registros: {{ $apartados->count() }}</p>
            <p>Total apartado: ${{ number_format($totalMonto, 2) }} | Total pagado: ${{ number_format($totalPagado, 2) }} | Saldo pendiente: ${{ number_format($totalSaldo, 2) }}</p>
            <p>Pan de Vida - Sistema de Control Interno</p>
        </div>
    @else
        <div class="empty">
            No hay registros para mostrar con los filtros aplicados
        </div>
    @endif
</body>
</html>
