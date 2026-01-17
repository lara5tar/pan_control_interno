# API de Abonos para App Móvil

Esta documentación describe los endpoints disponibles para gestionar abonos de apartados desde la aplicación móvil.

## Base URL
```
http://tu-dominio.com/api/v1/movil
```

---

## Endpoints Disponibles

### 1. Listar Todos los Apartados

Lista todos los apartados activos y vencidos (los que pueden recibir abonos). Útil para mostrar una lista completa sin necesidad de buscar.

**Endpoint:** `GET /apartados`

**Parámetros de Query (opcionales):**
- `estado` (string, opcional): Filtrar por estado
  - `activo`: Solo apartados activos
  - `vencido`: Solo apartados vencidos
  - `liquidado`: Solo apartados liquidados
  - `todos`: Todos los apartados (activos, vencidos y liquidados)
  - Sin especificar: Devuelve activos y vencidos (por defecto)
- `limite` (integer, opcional): Número máximo de resultados (por defecto: 50)

**Ejemplo de Request:**
```http
GET /api/v1/movil/apartados
GET /api/v1/movil/apartados?estado=activo
GET /api/v1/movil/apartados?estado=todos&limite=100
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "total": 25,
  "data": [
    {
      "id": 1,
      "folio": "APT-2025-001",
      "cliente": {
        "id": 5,
        "nombre": "Juan Pérez",
        "telefono": "5551234567"
      },
      "fecha_apartado": "2025-01-10",
      "fecha_limite": "2025-02-10",
      "monto_total": 500.00,
      "enganche": 100.00,
      "saldo_pendiente": 250.00,
      "total_pagado": 250.00,
      "porcentaje_pagado": 50.00,
      "estado": "activo",
      "observaciones": null,
      "libros": [
        {
          "codigo": "LIB001",
          "titulo": "Libro de Ejemplo",
          "precio_unitario": 250.00,
          "cantidad": 2,
          "subtotal": 500.00
        }
      ],
      "total_abonos": 1,
      "ultimo_abono": {
        "fecha": "2025-01-12",
        "monto": 150.00
      }
    },
    {
      "id": 3,
      "folio": "APT-2025-003",
      "cliente": {
        "id": 8,
        "nombre": "María García",
        "telefono": "5559876543"
      },
      "fecha_apartado": "2025-01-12",
      "fecha_limite": "2025-02-12",
      "monto_total": 300.00,
      "enganche": 80.00,
      "saldo_pendiente": 220.00,
      "total_pagado": 80.00,
      "porcentaje_pagado": 26.67,
      "estado": "activo",
      "observaciones": null,
      "libros": [
        {
          "codigo": "LIB002",
          "titulo": "Otro Libro",
          "precio_unitario": 150.00,
          "cantidad": 2,
          "subtotal": 300.00
        }
      ],
      "total_abonos": 0,
      "ultimo_abono": null
    }
  ]
}
```

**Respuestas de Error:**
- `404`: No se encontraron apartados
- `500`: Error del servidor

---

### 2. Buscar Apartado por Folio

Busca un apartado específico utilizando su número de folio.

**Endpoint:** `GET /apartados/buscar-folio/{folio}`

**Parámetros de URL:**
- `folio` (string, requerido): Número de folio del apartado

**Ejemplo de Request:**
```http
GET /api/v1/movil/apartados/buscar-folio/APT-2025-001
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "folio": "APT-2025-001",
    "cliente": {
      "id": 5,
      "nombre": "Juan Pérez",
      "telefono": "5551234567"
    },
    "fecha_apartado": "2025-01-10",
    "fecha_limite": "2025-02-10",
    "monto_total": 500.00,
    "enganche": 100.00,
    "saldo_pendiente": 250.00,
    "total_pagado": 250.00,
    "porcentaje_pagado": 50.00,
    "estado": "activo",
    "observaciones": null,
    "libros": [
      {
        "codigo": "LIB001",
        "titulo": "Libro de Ejemplo",
        "precio_unitario": 250.00,
        "cantidad": 2,
        "subtotal": 500.00
      }
    ],
    "total_abonos": 1,
    "ultimo_abono": {
      "fecha": "2025-01-12",
      "monto": 150.00
    }
  }
}
```

**Respuestas de Error:**
- `404`: Apartado no encontrado
- `400`: Apartado cancelado o liquidado
- `500`: Error del servidor

---

### 3. Buscar Apartados por Cliente

Busca todos los apartados activos de clientes cuyo nombre coincida con la búsqueda.

**Endpoint:** `GET /apartados/buscar-cliente`

**Parámetros de Query:**
- `nombre` (string, requerido): Nombre o parte del nombre del cliente

**Ejemplo de Request:**
```http
GET /api/v1/movil/apartados/buscar-cliente?nombre=Juan
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "cliente_id": 5,
      "nombre_cliente": "Juan Pérez",
      "telefono_cliente": "5551234567",
      "apartados": [
        {
          "id": 1,
          "folio": "APT-2025-001",
          "cliente": {
            "id": 5,
            "nombre": "Juan Pérez",
            "telefono": "5551234567"
          },
          "fecha_apartado": "2025-01-10",
          "fecha_limite": "2025-02-10",
          "monto_total": 500.00,
          "enganche": 100.00,
          "saldo_pendiente": 250.00,
          "total_pagado": 250.00,
          "porcentaje_pagado": 50.00,
          "estado": "activo",
          "observaciones": null,
          "libros": [
            {
              "codigo": "LIB001",
              "titulo": "Libro de Ejemplo",
              "precio_unitario": 250.00,
              "cantidad": 2,
              "subtotal": 500.00
            }
          ],
          "total_abonos": 1,
          "ultimo_abono": {
            "fecha": "2025-01-12",
            "monto": 150.00
          }
        }
      ]
    },
    {
      "cliente_id": 8,
      "nombre_cliente": "Juan García",
      "telefono_cliente": "5559876543",
      "apartados": [
        {
          "id": 3,
          "folio": "APT-2025-003",
          "cliente": {
            "id": 8,
            "nombre": "Juan García",
            "telefono": "5559876543"
          },
          "fecha_apartado": "2025-01-12",
          "fecha_limite": "2025-02-12",
          "monto_total": 300.00,
          "enganche": 80.00,
          "saldo_pendiente": 220.00,
          "total_pagado": 80.00,
          "porcentaje_pagado": 26.67,
          "estado": "activo",
          "observaciones": null,
          "libros": [
            {
              "codigo": "LIB002",
              "titulo": "Otro Libro",
              "precio_unitario": 150.00,
              "cantidad": 2,
              "subtotal": 300.00
            }
          ],
          "total_abonos": 0,
          "ultimo_abono": null
        }
      ]
    }
  ]
}
```

**Respuestas de Error:**
- `400`: Nombre no proporcionado
- `404`: No se encontraron clientes o apartados activos
- `500`: Error del servidor

---

### 4. Registrar Abono

Registra un nuevo abono a un apartado.

**Endpoint:** `POST /abonos`

**Headers:**
```
Content-Type: application/json
```

**Body Parameters:**
- `apartado_id` (integer, requerido): ID del apartado
- `monto` (decimal, requerido): Monto del abono (mayor a 0)
- `metodo_pago` (string, requerido): Método de pago - Valores permitidos: `efectivo`, `transferencia`, `tarjeta`
- `comprobante` (string, opcional): Número de comprobante o referencia
- `observaciones` (string, opcional): Notas adicionales (máx. 500 caracteres)
- `usuario` (string, requerido): Nombre del usuario que registra el abono (máx. 100 caracteres)

**Ejemplo de Request:**
```http
POST /api/v1/movil/abonos
Content-Type: application/json

{
  "apartado_id": 1,
  "monto": 150.00,
  "metodo_pago": "transferencia",
  "comprobante": "REF123456789",
  "observaciones": "Abono realizado desde app móvil",
  "usuario": "maria_lopez"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Abono registrado exitosamente",
  "data": {
    "abono": {
      "id": 5,
      "fecha_abono": "2025-01-15",
      "monto": 150.00,
      "saldo_anterior": 250.00,
      "saldo_nuevo": 100.00,
      "metodo_pago": "transferencia",
      "comprobante": "REF123456789",
      "observaciones": "Abono realizado desde app móvil"
    },
    "apartado": {
      "id": 1,
      "folio": "APT-2025-001",
      "cliente": {
        "id": 5,
        "nombre": "Juan Pérez",
        "telefono": "5551234567"
      },
      "fecha_apartado": "2025-01-10",
      "fecha_limite": "2025-02-10",
      "monto_total": 500.00,
      "enganche": 100.00,
      "saldo_pendiente": 100.00,
      "total_pagado": 400.00,
      "porcentaje_pagado": 80.00,
      "estado": "activo",
      "observaciones": null,
      "libros": [
        {
          "codigo": "LIB001",
          "titulo": "Libro de Ejemplo",
          "precio_unitario": 250.00,
          "cantidad": 2,
          "subtotal": 500.00
        }
      ],
      "total_abonos": 2,
      "ultimo_abono": {
        "fecha": "2025-01-15",
        "monto": 150.00
      }
    }
  }
}
```

**Nota:** Si el abono liquida completamente el apartado (saldo_pendiente llega a 0), el estado del apartado cambiará automáticamente a `liquidado`.

**Respuestas de Error:**

*422 - Errores de Validación:*
```json
{
  "success": false,
  "message": "Errores de validación",
  "errors": {
    "monto": ["El monto es requerido"],
    "metodo_pago": ["El método de pago debe ser: efectivo, transferencia o tarjeta"]
  }
}
```

*400 - Apartado Cancelado:*
```json
{
  "success": false,
  "message": "No se puede abonar a un apartado cancelado"
}
```

*400 - Apartado Liquidado:*
```json
{
  "success": false,
  "message": "Este apartado ya está liquidado"
}
```

*400 - Monto Excede Saldo:*
```json
{
  "success": false,
  "message": "El monto del abono excede el saldo pendiente",
  "saldo_pendiente": 100.00
}
```

*500 - Error del Servidor:*
```json
{
  "success": false,
  "message": "Error al registrar el abono",
  "error": "Descripción del error"
}
```

---

### 5. Historial de Abonos

Obtiene el historial completo de abonos de un apartado específico.

**Endpoint:** `GET /apartados/{apartado_id}/abonos`

**Parámetros de URL:**
- `apartado_id` (integer, requerido): ID del apartado

**Ejemplo de Request:**
```http
GET /api/v1/movil/apartados/1/abonos
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "apartado": {
      "id": 1,
      "folio": "APT-2025-001",
      "cliente": "Juan Pérez",
      "monto_total": 500.00,
      "saldo_pendiente": 100.00,
      "estado": "activo"
    },
    "abonos": [
      {
        "id": 3,
        "fecha_abono": "2025-01-12",
        "monto": 150.00,
        "saldo_anterior": 400.00,
        "saldo_nuevo": 250.00,
        "metodo_pago": "efectivo",
        "metodo_pago_label": "Efectivo",
        "comprobante": null,
        "observaciones": "Primer abono",
        "usuario": "carlos_mendez"
      },
      {
        "id": 5,
        "fecha_abono": "2025-01-15",
        "monto": 150.00,
        "saldo_anterior": 250.00,
        "saldo_nuevo": 100.00,
        "metodo_pago": "transferencia",
        "metodo_pago_label": "Transferencia",
        "comprobante": "REF123456789",
        "observaciones": "Abono realizado desde app móvil",
        "usuario": "maria_lopez"
      }
    ]
  }
}
```

**Respuestas de Error:**
- `404`: Apartado no encontrado
- `500`: Error del servidor

---

## Flujos de Uso

### Flujo 1: Listar Apartados y Abonar

1. **Listar apartados** disponibles:
   ```
   GET /api/v1/movil/apartados
   ```
   
2. El usuario **selecciona un apartado** de la lista

3. **Registrar abono** con el `apartado_id` seleccionado:
   ```
   POST /api/v1/movil/abonos
   {
     "apartado_id": 1,
     "monto": 150.00,
     "metodo_pago": "efectivo",
     "usuario": "nombre_usuario"
   }
   ```

### Flujo 2: Buscar por Folio y Abonar

1. **Buscar apartado** por folio:
   ```
   GET /api/v1/movil/apartados/buscar-folio/APT-2025-001
   ```

2. **Registrar abono** con el `apartado_id` obtenido:
   ```
   POST /api/v1/movil/abonos
   {
     "apartado_id": 1,
     "monto": 150.00,
     "metodo_pago": "efectivo",
     "usuario": "nombre_usuario"
   }
   ```

3. **Ver historial** (opcional):
   ```
   GET /api/v1/movil/apartados/1/abonos
   ```

### Flujo 3: Buscar por Cliente y Abonar

1. **Buscar apartados** del cliente:
   ```
   GET /api/v1/movil/apartados/buscar-cliente?nombre=Juan
   ```

2. El usuario **selecciona un apartado** de la lista devuelta

3. **Registrar abono** con el `apartado_id` seleccionado:
   ```
   POST /api/v1/movil/abonos
   {
     "apartado_id": 1,
     "monto": 150.00,
     "metodo_pago": "transferencia",
     "comprobante": "REF123456",
     "usuario": "nombre_usuario"
   }
   ```

---

## Códigos de Estado HTTP

- `200 OK`: Solicitud exitosa
- `201 Created`: Recurso creado exitosamente
- `400 Bad Request`: Solicitud inválida o estado del apartado no permite la operación
- `404 Not Found`: Recurso no encontrado
- `422 Unprocessable Entity`: Errores de validación
- `500 Internal Server Error`: Error del servidor

---

## Notas Importantes

1. **Estados de Apartado:**
   - `activo`: Puede recibir abonos
   - `vencido`: Puede recibir abonos (fecha límite pasada pero no cancelado)
   - `cancelado`: NO puede recibir abonos
   - `liquidado`: NO puede recibir abonos (ya está pagado completamente)

2. **Liquidación Automática:**
   - Cuando un abono hace que el `saldo_pendiente` llegue a 0, el apartado se marca automáticamente como `liquidado`

3. **Validación de Monto:**
   - El monto del abono no puede exceder el `saldo_pendiente` del apartado
   - El monto debe ser mayor a 0

4. **Métodos de Pago:**
   - `efectivo`: Efectivo
   - `transferencia`: Transferencia bancaria
   - `tarjeta`: Tarjeta de crédito/débito

5. **Búsqueda por Cliente:**
   - La búsqueda es parcial (usa LIKE)
   - Solo devuelve apartados con estado `activo` o `vencido`
   - Agrupa los apartados por cliente

---

## Ejemplos de Integración

### JavaScript/Fetch API

```javascript
// Listar todos los apartados
async function listarApartados(estado = null, limite = 50) {
  let url = 'http://tu-dominio.com/api/v1/movil/apartados';
  const params = new URLSearchParams();
  if (estado) params.append('estado', estado);
  if (limite) params.append('limite', limite);
  
  if (params.toString()) url += '?' + params.toString();
  
  const response = await fetch(url);
  const data = await response.json();
  return data;
}

// Buscar apartado por folio
async function buscarPorFolio(folio) {
  const response = await fetch(
    `http://tu-dominio.com/api/v1/movil/apartados/buscar-folio/${folio}`
  );
  const data = await response.json();
  return data;
}

// Registrar abono
async function registrarAbono(apartadoId, monto, metodoPago, usuario) {
  const response = await fetch(
    'http://tu-dominio.com/api/v1/movil/abonos',
    {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        apartado_id: apartadoId,
        monto: monto,
        metodo_pago: metodoPago,
        usuario: usuario
      })
    }
  );
  const data = await response.json();
  return data;
}
```

### Flutter/Dart

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

// Listar todos los apartados
Future<Map<String, dynamic>> listarApartados({
  String? estado,
  int limite = 50,
}) async {
  var url = 'http://tu-dominio.com/api/v1/movil/apartados';
  final params = <String, String>{};
  if (estado != null) params['estado'] = estado;
  params['limite'] = limite.toString();
  
  final uri = Uri.parse(url).replace(queryParameters: params);
  final response = await http.get(uri);
  return json.decode(response.body);
}

// Buscar apartado por folio
Future<Map<String, dynamic>> buscarPorFolio(String folio) async {
  final response = await http.get(
    Uri.parse('http://tu-dominio.com/api/v1/movil/apartados/buscar-folio/$folio'),
  );
  return json.decode(response.body);
}

// Registrar abono
Future<Map<String, dynamic>> registrarAbono(
  int apartadoId,
  double monto,
  String metodoPago,
  String usuario,
) async {
  final response = await http.post(
    Uri.parse('http://tu-dominio.com/api/v1/movil/abonos'),
    headers: {'Content-Type': 'application/json'},
    body: json.encode({
      'apartado_id': apartadoId,
      'monto': monto,
      'metodo_pago': metodoPago,
      'usuario': usuario,
    }),
  );
  return json.decode(response.body);
}
```

---

## Soporte

Para reportar problemas o sugerencias, contacta al equipo de desarrollo.
