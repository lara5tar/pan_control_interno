<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario</title>
    <style>
        {!! $styles !!}
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE INVENTARIO DE LIBROS</h1>
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

    @if($libros->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Código de Barras</th>
                    <th class="text-right">Precio</th>
                    <th class="text-center">Stock</th>
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
                            <span class="badge {{ $libro->stock > 10 ? 'badge-success' : ($libro->stock > 0 ? 'badge-warning' : 'badge-danger') }}">
                                {{ $libro->stock }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>Total de registros: {{ $libros->count() }}</p>
            <p>Pan de Vida - Sistema de Control Interno</p>
        </div>
    @else
        <div class="empty">
            No hay registros para mostrar con los filtros aplicados
        </div>
    @endif
</body>
</html>
