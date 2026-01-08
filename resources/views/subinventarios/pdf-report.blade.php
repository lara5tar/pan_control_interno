<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Sub-Inventarios</title>
    <style>
        {!! $styles !!}
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE SUB-INVENTARIOS</h1>
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

    @if($subinventarios->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha Sub-Inventario</th>
                    <th>Descripción</th>
                    <th class="text-center">Libros</th>
                    <th class="text-center">Unidades Totales</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalLibros = 0;
                    $totalUnidades = 0;
                @endphp
                @foreach($subinventarios as $subinventario)
                    @php
                        $totalLibros += $subinventario->libros->count();
                        $totalUnidades += $subinventario->total_unidades;
                    @endphp
                    <tr>
                        <td>{{ $subinventario->id }}</td>
                        <td>{{ $subinventario->fecha_subinventario->format('d/m/Y') }}</td>
                        <td>{{ $subinventario->descripcion }}</td>
                        <td class="text-center">{{ $subinventario->libros->count() }}</td>
                        <td class="text-center">{{ $subinventario->total_unidades }}</td>
                        <td class="text-center">
                            <span class="badge {{ $subinventario->estado === 'activo' ? 'badge-warning' : ($subinventario->estado === 'completado' ? 'badge-success' : 'badge-danger') }}">
                                {{ $subinventario->estado === 'activo' ? 'Activo' : ($subinventario->estado === 'completado' ? 'Completado' : 'Cancelado') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f3f4f6;">
                    <td colspan="3" style="text-align: right;">TOTALES:</td>
                    <td class="text-center">{{ $totalLibros }}</td>
                    <td class="text-center">{{ $totalUnidades }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>Total de registros: {{ $subinventarios->count() }}</p>
            <p>Total libros diferentes: {{ $totalLibros }} | Total unidades: {{ $totalUnidades }}</p>
            <p>Pan de Vida - Sistema de Control Interno</p>
        </div>
    @else
        <div class="empty">
            No hay registros para mostrar con los filtros aplicados
        </div>
    @endif
</body>
</html>
