<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Libros en Sub-Inventario</title>
    <style>
        {!! $styles !!}
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE LIBROS EN SUB-INVENTARIO</h1>
        <p>Generado el {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filters">
        <h3>Información del Sub-Inventario:</h3>
        <ul>
            <li><strong>ID:</strong> {{ $subinventario->id }}</li>
            <li><strong>Descripción:</strong> {{ $subinventario->descripcion ?: 'Sub-Inventario #' . $subinventario->id }}</li>
            <li><strong>Fecha:</strong> {{ $subinventario->fecha_subinventario->format('d/m/Y') }}</li>
            <li><strong>Estado:</strong> 
                @if($subinventario->estado === 'activo')
                    <span class="badge badge-success">Activo</span>
                @elseif($subinventario->estado === 'completado')
                    <span class="badge badge-info">Completado</span>
                @else
                    <span class="badge badge-danger">Cancelado</span>
                @endif
            </li>
            <li><strong>Usuario:</strong> {{ $subinventario->usuario }}</li>
        </ul>
    </div>

    @if($libros->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Código de Barras</th>
                    <th class="text-right">Precio</th>
                    <th class="text-center">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($libros as $libro)
                    <tr>
                        <td>{{ $libro->id }}</td>
                        <td>{{ $libro->nombre }}</td>
                        <td>{{ $libro->codigo_barras ?? 'Sin código' }}</td>
                        <td class="text-right">${{ number_format($libro->precio, 2) }}</td>
                        <td class="text-center">
                            <span class="badge badge-info">
                                {{ $libro->pivot->cantidad }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p><strong>Total de libros:</strong> {{ $libros->count() }}</p>
            <p><strong>Total de unidades:</strong> {{ $libros->sum('pivot.cantidad') }}</p>
            <p style="margin-top: 20px;">Pan de Vida - Sistema de Control Interno</p>
        </div>
    @else
        <div class="empty">
            No hay libros en este sub-inventario
        </div>
    @endif
</body>
</html>
