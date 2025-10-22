<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 10px;
            color: #666;
        }
        
        .filters {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 20px;
            border-left: 3px solid #333;
        }
        
        .filters h3 {
            font-size: 12px;
            margin-bottom: 8px;
        }
        
        .filters ul {
            list-style: none;
            padding-left: 10px;
        }
        
        .filters li {
            font-size: 10px;
            margin-bottom: 3px;
            color: #555;
        }
        
        .filters li:before {
            content: "• ";
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        thead {
            background-color: #333;
            color: white;
        }
        
        th {
            padding: 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tbody tr:hover {
            background-color: #f0f0f0;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .empty {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
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
                        <td class="text-center">{{ $libro->stock }}</td>
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
