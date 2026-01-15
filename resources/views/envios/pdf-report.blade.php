<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Envíos</title>
    <style>
        {!! $styles !!}
        
        /* Estilos específicos para envíos */
        .envio-table td {
            padding: 8px;
            font-size: 11px;
        }
        
        .estado-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
        }
        
        .estado-pendiente { background: #FEF3C7; color: #92400E; }
        .estado-pagado { background: #D1FAE5; color: #065F46; }
        
        .detalle-ventas {
            background: #F9FAFB;
            padding: 8px;
            margin: 4px 0;
            border-left: 3px solid #3B82F6;
            font-size: 10px;
        }
        
        .venta-item {
            margin: 3px 0;
            padding: 3px;
        }
        
        .tipo-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .tipo-automatico { background: #DBEAFE; color: #1E40AF; }
        .tipo-manual { background: #E0E7FF; color: #4338CA; }
        
        .resumen-totales {
            background: #F0F9FF;
            border: 2px solid #3B82F6;
            padding: 12px;
            margin: 20px 0;
            border-radius: 6px;
        }
        
        .resumen-totales table {
            width: 100%;
            font-size: 12px;
        }
        
        .resumen-totales td {
            padding: 6px;
        }
        
        .total-principal {
            font-size: 18px;
            font-weight: bold;
            color: #1E40AF;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE ENVÍOS</h1>
        <p class="subtitle">Pan de Vida - Control de Envíos a FedEx</p>
        <p class="date">Generado: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @if($filtros && count($filtros) > 0)
        <div class="filters">
            <strong>Filtros aplicados:</strong>
            <ul>
                @foreach($filtros as $filtro)
                    <li>{{ $filtro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total de Envíos</div>
            <div class="stat-value">{{ $estadisticas['total'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Ventas Incluidas</div>
            <div class="stat-value">{{ $envios->sum(function($e) { return $e->ventas->count(); }) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Monto Total Envíos</div>
            <div class="stat-value">${{ number_format($estadisticas['monto_total'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pendientes Pago</div>
            <div class="stat-value">{{ $estadisticas['pendientes'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pagados</div>
            <div class="stat-value">{{ $envios->where('estado_pago', 'pagado')->count() }}</div>
        </div>
    </div>

    @if($envios->count() > 0)
        <!-- Resumen Global -->
        <div class="resumen-totales">
            <h3 style="margin: 0 0 10px 0; color: #1E40AF;">RESUMEN GLOBAL DE ENVIOS</h3>
            <table>
                <tr>
                    <td style="width: 50%;"><strong>Total de Envíos:</strong></td>
                    <td style="text-align: right;">{{ $envios->count() }} envíos</td>
                </tr>
                <tr>
                    <td><strong>Total de Ventas Incluidas:</strong></td>
                    <td style="text-align: right;">{{ $envios->sum(function($e) { return $e->ventas->count(); }) }} ventas</td>
                </tr>
                <tr>
                    <td><strong>Total Ventas (Productos):</strong></td>
                    <td style="text-align: right;">${{ number_format($envios->sum(function($e) { return $e->calcularTotalVentas(); }), 2) }}</td>
                </tr>
                <tr style="border-top: 2px solid #3B82F6;">
                    <td><strong>TOTAL COSTOS DE ENVÍO (FedEx):</strong></td>
                    <td style="text-align: right;"><span class="total-principal">${{ number_format($envios->sum('monto_a_pagar'), 2) }}</span></td>
                </tr>
            </table>
        </div>

        <h2 style="margin-top: 20px; color: #1F2937; border-bottom: 2px solid #3B82F6; padding-bottom: 5px;">
            Detalle de Envíos
        </h2>

        @foreach($envios as $envio)
            <div style="margin-bottom: 20px; page-break-inside: avoid;">
                <table class="table envio-table" style="margin-bottom: 8px;">
                    <thead>
                        <tr style="background: #1E40AF; color: white;">
                            <th colspan="6" style="padding: 8px; text-align: left;">
                                <strong>Envío #{{ $envio->id }}</strong>
                                @if($envio->guia)
                                    - Guía: {{ $envio->guia }}
                                @endif
                                @if($envio->tipo_generacion === 'automatico')
                                    <span class="tipo-badge tipo-automatico" style="float: right; margin-left: 10px;">AUTOMÁTICO</span>
                                @else
                                    <span class="tipo-badge tipo-manual" style="float: right; margin-left: 10px;">MANUAL</span>
                                @endif
                                <span class="estado-badge estado-{{ $envio->estado_pago }}" style="float: right;">
                                    {{ $envio->getEstadoLabel() }}
                                </span>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 15%;">Fecha Envío</th>
                            <th style="width: 25%;">Periodo</th>
                            <th style="width: 15%;">Ventas</th>
                            <th style="width: 15%;">Total Ventas</th>
                            <th style="width: 15%;">Costos Envío</th>
                            <th style="width: 15%;">Monto FedEx</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $envio->fecha_envio->format('d/m/Y') }}</td>
                            <td>
                                @if($envio->periodo_inicio && $envio->periodo_fin)
                                    {{ $envio->periodo_inicio->format('d/m/Y') }} - {{ $envio->periodo_fin->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td><strong>{{ $envio->ventas->count() }}</strong></td>
                            <td>${{ number_format($envio->calcularTotalVentas(), 2) }}</td>
                            <td style="color: #3B82F6; font-weight: bold;">${{ number_format($envio->ventas->sum('costo_envio'), 2) }}</td>
                            <td style="background: #EFF6FF; font-weight: bold; color: #1E40AF;">${{ number_format($envio->monto_a_pagar, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                @if($envio->ventas->count() > 0)
                    <div class="detalle-ventas">
                        <strong>📦 Ventas Incluidas en este Envío:</strong>
                        <table style="width: 100%; margin-top: 5px; font-size: 9px;">
                            <thead>
                                <tr style="background: #E5E7EB;">
                                    <th style="padding: 4px; text-align: left; width: 8%;">ID</th>
                                    <th style="padding: 4px; text-align: left; width: 12%;">Fecha</th>
                                    <th style="padding: 4px; text-align: left; width: 25%;">Cliente</th>
                                    <th style="padding: 4px; text-align: center; width: 10%;">Libros</th>
                                    <th style="padding: 4px; text-align: right; width: 15%;">Total Venta</th>
                                    <th style="padding: 4px; text-align: right; width: 15%;">Costo Envío</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($envio->ventas as $venta)
                                    <tr>
                                        <td style="padding: 3px;">#{{ $venta->id }}</td>
                                        <td style="padding: 3px;">{{ $venta->fecha_venta->format('d/m/Y') }}</td>
                                        <td style="padding: 3px;">{{ $venta->cliente?->nombre ?? 'Sin cliente' }}</td>
                                        <td style="padding: 3px; text-align: center;">{{ $venta->movimientos->sum('cantidad') }}</td>
                                        <td style="padding: 3px; text-align: right;">${{ number_format($venta->total, 2) }}</td>
                                        <td style="padding: 3px; text-align: right; font-weight: bold; color: #3B82F6;">${{ number_format($venta->costo_envio, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr style="background: #F3F4F6; font-weight: bold; border-top: 2px solid #3B82F6;">
                                    <td colspan="4" style="padding: 5px; text-align: right;">SUBTOTALES:</td>
                                    <td style="padding: 5px; text-align: right;">${{ number_format($envio->ventas->sum('total'), 2) }}</td>
                                    <td style="padding: 5px; text-align: right; color: #1E40AF;">${{ number_format($envio->ventas->sum('costo_envio'), 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($envio->notas)
                    <div style="margin-top: 5px; padding: 5px; background: #FFFBEB; border-left: 3px solid #F59E0B; font-size: 9px;">
                        <strong>📝 Notas:</strong> {{ $envio->notas }}
                    </div>
                @endif

                @if($envio->fecha_pago)
                    <div style="margin-top: 5px; padding: 5px; background: #D1FAE5; border-left: 3px solid #059669; font-size: 9px;">
                        <strong>✅ Pagado el:</strong> {{ $envio->fecha_pago->format('d/m/Y') }}
                        @if($envio->referencia_pago)
                            - <strong>Ref:</strong> {{ $envio->referencia_pago }}
                        @endif
                    </div>
                @endif
            </div>
        @endforeach

        <!-- Totales Finales -->
        <div class="resumen-totales" style="margin-top: 30px; page-break-inside: avoid;">
            <h3 style="margin: 0 0 10px 0; color: #1E40AF;">TOTALES FINALES DEL REPORTE</h3>
            <table>
                <tr>
                    <td style="width: 50%;"><strong>Total de Envíos Procesados:</strong></td>
                    <td style="text-align: right; font-size: 14px; font-weight: bold;">{{ $envios->count() }}</td>
                </tr>
                <tr>
                    <td><strong>Total de Ventas Incluidas:</strong></td>
                    <td style="text-align: right; font-size: 14px; font-weight: bold;">{{ $envios->sum(function($e) { return $e->ventas->count(); }) }}</td>
                </tr>
                <tr>
                    <td><strong>Suma Total de Ventas (Productos):</strong></td>
                    <td style="text-align: right; font-size: 14px;">${{ number_format($envios->sum(function($e) { return $e->calcularTotalVentas(); }), 2) }}</td>
                </tr>
                <tr style="border-top: 3px solid #1E40AF;">
                    <td><strong style="font-size: 14px;">TOTAL A PAGAR A FEDEX:</strong></td>
                    <td style="text-align: right;"><span class="total-principal" style="font-size: 20px;">${{ number_format($envios->sum('monto_a_pagar'), 2) }}</span></td>
                </tr>
            </table>
        </div>
    @else
        <div class="empty-state">
            <p>No hay envíos para mostrar con los filtros aplicados</p>
        </div>
    @endif

    <div class="footer">
        <p>Pan de Vida - Control Interno</p>
        <p>Página {PAGENO} de {nbpg}</p>
    </div>
</body>
</html>
