# API Routes

## SubInventarios

**GET** `/api/v1/subinventarios` - Obtener lista de subinventarios
- Filtros: `estado`, `fecha`, `search`, `ordenar` (reciente|antiguo|fecha_asc|fecha_desc), `per_page`

## Libros

**GET** `/api/v1/libros/buscar-codigo/{codigo}` - Buscar libro por código de barras o QR
- `{codigo}` (obligatorio) - Código de barras del libro

## Clientes

**GET** `/api/v1/clientes` - Obtener lista de clientes
- Filtros: `search`, `per_page`

## Ventas

**POST** `/api/v1/ventas` - Crear nueva venta
- `subinventario_id` (obligatorio) - ID del subinventario
- `cliente_id` (opcional) - ID del cliente
- `fecha_venta` (obligatorio) - Fecha de la venta
- `tipo_pago` (obligatorio) - contado|credito|mixto
- `descuento_global` (opcional) - Porcentaje 0-100
- `observaciones` (opcional) - Texto máximo 500 caracteres
- `usuario` (obligatorio) - Nombre del usuario
- `libros` (obligatorio, array mínimo 1):
  - `libro_id` (obligatorio) - ID del libro
  - `cantidad` (obligatorio) - Cantidad mínimo 1
  - `descuento` (opcional) - Porcentaje 0-100
