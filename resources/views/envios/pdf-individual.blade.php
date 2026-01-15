<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Envío #{{ $envio->id }}</title>
    <style>
        {!! $styles !!}
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .info-item {
            padding: 8px;
            background: #F9FAFB;
            border-left: 3px solid #3B82F6;
        }
        
        .info-label {
            font-size: 10px;
            color: #6B7280;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .info-value {
            font-size: 12px;
            color: #1F2937;
        }
        
        .section-title {
            background: #1E40AF;
            color: white;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            border-radius: 4px;
        }
        
        .resumen-box {
            background: #E0F2FE;
            border: 2px solid #3B82F6;
            padding: 15px;
            margin: 15px 0;
            border-radius: 6px;
        }
        
        .resumen-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 11px;
        }
        
        .resumen-item.total {
            border-top: 2px solid #1E40AF;
            margin-top: 8px;
            padding-top: 8px;
            font-size: 14px;
            font-weight: bold;
            color: #1E40AF;
        }
        
        .tipo-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .tipo-automatico {
            background: #DBEAFE;
            color: #1E40AF;
        }
        
        .tipo-manual {
            background: #E0E7FF;
            color: #4338CA;
        }
        
        .estado-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .estado-pendiente {
            background: #FEF3C7;
            color: #92400E;
        }
        
        .estado-pagado {
            background: #D1FAE5;
            color: #065F46;
        }
        
        .venta-detail {
            background: #F9FAFB;
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #3B82F6;
            page-break-inside: avoid;
        }
        
        .venta-header {
            font-weight: bold;
            color: #1E40AF;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .table {
            font-size: 10px;
        }
        
        .table td {
            padding: 6px;
        }
        
        .highlight-envio {
            background: #EFF6FF;
            font-weight: bold;
            color: #1E40AF;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE ENVÍO #{{ $envio->id }}</h1>
        <p class="subtitle">Pan de Vida - Detalle Completo del Envío</p>
        <p class="date">Generado: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Información del Envío -->
    <div class="section-title">INFORMACION DEL ENVIO</div>
    
    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">ID de Envío</div>
            <div class="info-value">#{{ $envio->id }}</div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Estado de Pago</div>
            <div class="info-value">
                <span class="estado-badge estado-{{ $envio->estado_pago }}">
                    {{ $envio->getEstadoLabel() }}
                </span>
            </div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Guía / Referencia</div>
            <div class="info-value">{{ $envio->guia ?: 'Sin guía' }}</div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Tipo de Generación</div>
            <div class="info-value">
                <span class="tipo-badge tipo-{{ $envio->tipo_generacion === 'automatico' ? 'automatico' : 'manual' }}">
                    {{ $envio->tipo_generacion === 'automatico' ? 'AUTOMÁTICO' : 'MANUAL' }}
                </span>
            </div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Fecha de Envío</div>
            <div class="info-value">{{ $envio->fecha_envio->format('d/m/Y') }}</div>
        </div>
        
        @if($envio->periodo_inicio && $envio->periodo_fin)
            <div class="info-item">
                <div class="info-label">Periodo</div>
                <div class="info-value">{{ $envio->periodo_inicio->format('d/m/Y') }} - {{ $envio->periodo_fin->format('d/m/Y') }}</div>
            </div>
        @endif
        
        @if($envio->fecha_pago)
            <div class="info-item">
                <div class="info-label">Fecha de Pago</div>
                <div class="info-value">{{ $envio->fecha_pago->format('d/m/Y') }}</div>
            </div>
        @endif
        
        @if($envio->referencia_pago)
            <div class="info-item">
                <div class="info-label">Referencia de Pago</div>
                <div class="info-value">{{ $envio->referencia_pago }}</div>
            </div>
        @endif
        
        @if($envio->usuario)
            <div class="info-item" style="grid-column: 1 / -1;">
                <div class="info-label">Registrado por</div>
                <div class="info-value">{{ $envio->usuario }}</div>
            </div>
        @endif
    </div>

    <!-- Resumen Financiero -->
    <div class="section-title">RESUMEN FINANCIERO</div>
    
    <div class="resumen-box">
        <div class="resumen-item">
            <span><strong>Cantidad de Ventas:</strong></span>
            <span>{{ $envio->ventas->count() }}</span>
        </div>
        <div class="resumen-item">
            <span><strong>Total de Libros:</strong></span>
            <span>{{ $envio->total_libros }} unidades</span>
        </div>
        <div class="resumen-item">
            <span><strong>Total Ventas (Productos):</strong></span>
            <span>${{ number_format($envio->calcularTotalVentas(), 2) }}</span>
        </div>
        <div class="resumen-item">
            <span><strong>Total Costos de Envío:</strong></span>
            <span style="color: #3B82F6; font-weight: bold;">${{ number_format($envio->ventas->sum('costo_envio'), 2) }}</span>
        </div>
        <div class="resumen-item total">
            <span>MONTO A PAGAR FEDEX:</span>
            <span>${{ number_format($envio->monto_a_pagar, 2) }}</span>
        </div>
    </div>

    <!-- Detalle de Ventas -->
    <div class="section-title">DETALLE DE VENTAS</div>
    
    <table class="table">
        <thead>
            <tr>
                <th style="width: 10%;">ID</th>
                <th style="width: 12%;">Fecha</th>
                <th style="width: 30%;">Cliente</th>
                <th style="width: 10%; text-align: center;">Libros</th>
                <th style="width: 19%; text-align: right;">Total Venta</th>
                <th style="width: 19%; text-align: right;">Costo Envío</th>
            </tr>
        </thead>
        <tbody>
            @foreach($envio->ventas as $venta)
                <tr>
                    <td>#{{ $venta->id }}</td>
                    <td>{{ $venta->fecha_venta->format('d/m/Y') }}</td>
                    <td>{{ $venta->cliente?->nombre ?? 'Sin cliente' }}</td>
                    <td style="text-align: center;">{{ $venta->movimientos->sum('cantidad') }}</td>
                    <td style="text-align: right;">${{ number_format($venta->total, 2) }}</td>
                    <td style="text-align: right;" class="highlight-envio">${{ number_format($venta->costo_envio, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #F3F4F6; font-weight: bold;">
                <td colspan="4" style="text-align: right;">TOTALES:</td>
                <td style="text-align: right;">${{ number_format($envio->ventas->sum('total'), 2) }}</td>
                <td style="text-align: right;" class="highlight-envio">${{ number_format($envio->ventas->sum('costo_envio'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Detalle de Libros por Venta -->
    @if($envio->ventas->count() > 0)
        <div class="section-title" style="margin-top: 30px;">DETALLE DE LIBROS POR VENTA</div>
        
        @foreach($envio->ventas as $venta)
            <div class="venta-detail">
                <div class="venta-header">
                    Venta #{{ $venta->id }} - {{ $venta->cliente?->nombre ?? 'Sin cliente' }}
                    <span style="float: right; font-weight: normal; color: #6B7280;">
                        Fecha: {{ $venta->fecha_venta->format('d/m/Y') }}
                    </span>
                </div>
                
                <table class="table" style="margin-top: 5px;">
                    <thead>
                        <tr style="background: #E5E7EB;">
                            <th style="width: 15%;">Código</th>
                            <th style="width: 40%;">Título</th>
                            <th style="width: 10%; text-align: center;">Cant.</th>
                            <th style="width: 17%; text-align: right;">Precio Unit.</th>
                            <th style="width: 18%; text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($venta->movimientos as $movimiento)
                            <tr>
                                <td>{{ $movimiento->libro->codigo_barras ?? 'N/A' }}</td>
                                <td>{{ $movimiento->libro->nombre ?? 'N/A' }}</td>
                                <td style="text-align: center;">{{ $movimiento->cantidad }}</td>
                                <td style="text-align: right;">${{ number_format($movimiento->precio_unitario, 2) }}</td>
                                <td style="text-align: right;">${{ number_format($movimiento->precio_unitario * $movimiento->cantidad, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #F9FAFB; font-weight: bold;">
                            <td colspan="4" style="text-align: right;">Total Venta:</td>
                            <td style="text-align: right;">${{ number_format($venta->total, 2) }}</td>
                        </tr>
                        <tr style="background: #EFF6FF; font-weight: bold;">
                            <td colspan="4" style="text-align: right;">Costo de Envío:</td>
                            <td style="text-align: right; color: #1E40AF;">${{ number_format($venta->costo_envio, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach
    @endif

    <!-- Notas -->
    @if($envio->notas)
        <div class="section-title">NOTAS</div>
        <div style="background: #FFFBEB; border-left: 4px solid #F59E0B; padding: 10px; margin: 10px 0; font-size: 11px;">
            {{ $envio->notas }}
        </div>
    @endif

    <div class="footer">
        <p>Pan de Vida - Control Interno</p>
        <p>Envío #{{ $envio->id }} - Página {PAGENO} de {nbpg}</p>
    </div>
</body>
</html>
