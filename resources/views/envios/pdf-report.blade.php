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
        .estado-en_transito { background: #DBEAFE; color: #1E40AF; }
        .estado-entregado { background: #D1FAE5; color: #065F46; }
        .estado-cancelado { background: #FEE2E2; color: #991B1B; }
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
            <div class="stat-label">Monto Total</div>
            <div class="stat-value">${{ number_format($estadisticas['monto_total'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pendientes</div>
            <div class="stat-value">{{ $estadisticas['pendientes'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">En Tránsito</div>
            <div class="stat-value">{{ $estadisticas['en_transito'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Entregados</div>
            <div class="stat-value">{{ $estadisticas['entregados'] }}</div>
        </div>
        @if($estadisticas['cancelados'] > 0)
            <div class="stat-card">
                <div class="stat-label">Cancelados</div>
                <div class="stat-value">{{ $estadisticas['cancelados'] }}</div>
            </div>
        @endif
    </div>

    @if($envios->count() > 0)
        <table class="table envio-table">
            <thead>
                <tr>
                    <th style="width: 8%;">ID</th>
                    <th style="width: 18%;">Guía</th>
                    <th style="width: 12%;">Fecha</th>
                    <th style="width: 12%;">Ventas</th>
                    <th style="width: 15%;">Monto FedEx</th>
                    <th style="width: 15%;">Estado</th>
                    <th style="width: 20%;">Notas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($envios as $envio)
                    <tr>
                        <td>#{{ $envio->id }}</td>
                        <td>{{ $envio->guia ?: 'Sin guía' }}</td>
                        <td>{{ $envio->fecha_envio->format('d/m/Y') }}</td>
                        <td>{{ $envio->ventas->count() }} ventas</td>
                        <td>${{ number_format($envio->monto_a_pagar, 2) }}</td>
                        <td>
                            <span class="estado-badge estado-{{ $envio->estado }}">
                                {{ $envio->getEstadoLabel() }}
                            </span>
                        </td>
                        <td>{{ Str::limit($envio->notas, 30) ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL:</td>
                    <td style="font-weight: bold;">${{ number_format($envios->sum('monto_a_pagar'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
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
