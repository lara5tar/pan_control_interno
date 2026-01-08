# API de Ventas - App Móvil

## 📱 Crear Venta desde Punto de Venta Móvil

Este documento explica cómo crear ventas desde tu aplicación móvil usando el endpoint de API.

---

## 🌐 Endpoint

```
POST /api/v1/ventas
```

**URL Completa:**
```
https://inventario.sistemasdevida.com/api/v1/ventas
```

---

## 📋 Parámetros del Request

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Body (JSON)

#### Campos Obligatorios

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `subinventario_id` | integer | ID del punto de venta/subinventario |
| `cod_congregante` | string | Token del usuario (para validar acceso) |
| `fecha_venta` | date | Fecha de la venta (formato: YYYY-MM-DD) |
| `tipo_pago` | string | Tipo de pago: `contado`, `credito`, `mixto` |
| `usuario` | string | Nombre del usuario que realiza la venta |
| `libros` | array | Array de libros vendidos (mínimo 1) |
| `libros[].libro_id` | integer | ID del libro |
| `libros[].cantidad` | integer | Cantidad vendida (mínimo 1) |

#### Campos Opcionales

| Campo | Tipo | Descripción | Default |
|-------|------|-------------|---------|
| `cliente_id` | integer | ID del cliente (obligatorio si `tipo_pago=credito`) | null |
| `descuento_global` | decimal | Descuento general en porcentaje (0-100) | 0 |
| `observaciones` | string | Notas adicionales (máx 500 caracteres) | null |
| `libros[].descuento` | decimal | Descuento individual del libro (0-100) | 0 |
| `tiene_envio` | boolean | Si la venta incluye envío | false |
| `costo_envio` | decimal | Costo del envío | 0 |
| `direccion_envio` | string | Dirección de entrega (máx 500 caracteres) | null |
| `telefono_envio` | string | Teléfono de contacto (máx 20 caracteres) | null |

---

## 📝 Ejemplos de Uso

### Ejemplo 1: Venta Simple al Contado

```json
{
  "subinventario_id": 1,
  "cod_congregante": "14279",
  "fecha_venta": "2026-01-08",
  "tipo_pago": "contado",
  "usuario": "Juan Pérez",
  "libros": [
    {
      "libro_id": 12,
      "cantidad": 2
    },
    {
      "libro_id": 23,
      "cantidad": 1
    }
  ]
}
```

**cURL:**
```bash
curl -X POST "https://inventario.sistemasdevida.com/api/v1/ventas" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "subinventario_id": 1,
    "cod_congregante": "14279",
    "fecha_venta": "2026-01-08",
    "tipo_pago": "contado",
    "usuario": "Juan Pérez",
    "libros": [
      {"libro_id": 12, "cantidad": 2},
      {"libro_id": 23, "cantidad": 1}
    ]
  }'
```

---

### Ejemplo 2: Venta con Cliente y Descuento

```json
{
  "subinventario_id": 1,
  "cod_congregante": "14279",
  "cliente_id": 5,
  "fecha_venta": "2026-01-08",
  "tipo_pago": "contado",
  "usuario": "María González",
  "descuento_global": 10,
  "observaciones": "Cliente frecuente - descuento especial",
  "libros": [
    {
      "libro_id": 12,
      "cantidad": 3,
      "descuento": 5
    },
    {
      "libro_id": 23,
      "cantidad": 2,
      "descuento": 0
    }
  ]
}
```

**JavaScript/React Native:**
```javascript
async function crearVentaConDescuento() {
  const response = await fetch('https://inventario.sistemasdevida.com/api/v1/ventas', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      subinventario_id: 1,
      cod_congregante: await AsyncStorage.getItem('codCongregante'),
      cliente_id: 5,
      fecha_venta: new Date().toISOString().split('T')[0],
      tipo_pago: 'contado',
      usuario: await AsyncStorage.getItem('username'),
      descuento_global: 10,
      observaciones: 'Cliente frecuente - descuento especial',
      libros: [
        { libro_id: 12, cantidad: 3, descuento: 5 },
        { libro_id: 23, cantidad: 2, descuento: 0 }
      ]
    })
  });
  
  const data = await response.json();
  
  if (data.success) {
    console.log('Venta creada:', data.data);
  } else {
    console.error('Error:', data.message);
  }
}
```

---

### Ejemplo 3: Venta a Crédito

```json
{
  "subinventario_id": 1,
  "cod_congregante": "14279",
  "cliente_id": 8,
  "fecha_venta": "2026-01-08",
  "tipo_pago": "credito",
  "usuario": "Pedro Martínez",
  "observaciones": "Pago en 3 cuotas",
  "libros": [
    {
      "libro_id": 156,
      "cantidad": 5
    }
  ]
}
```

**Nota:** ⚠️ Para ventas a crédito, el campo `cliente_id` es **obligatorio**.

---

### Ejemplo 4: Venta con Envío

```json
{
  "subinventario_id": 1,
  "cod_congregante": "14279",
  "cliente_id": 3,
  "fecha_venta": "2026-01-08",
  "tipo_pago": "contado",
  "usuario": "Ana López",
  "tiene_envio": true,
  "costo_envio": 150.00,
  "direccion_envio": "Calle Principal #123, Sector Los Jardines, Santo Domingo",
  "telefono_envio": "809-555-1234",
  "observaciones": "Envío programado para mañana",
  "libros": [
    {
      "libro_id": 178,
      "cantidad": 1
    }
  ]
}
```

---

## ✅ Respuestas del API

### Respuesta Exitosa (201 Created)

```json
{
  "success": true,
  "message": "Venta creada exitosamente",
  "data": {
    "venta_id": 145,
    "subtotal": 2850.00,
    "descuento": 285.00,
    "costo_envio": 150.00,
    "total": 2715.00,
    "total_pagado": 2715.00,
    "saldo_pendiente": 0.00,
    "estado_pago": "completado",
    "tiene_envio": true,
    "envio_id": 23
  }
}
```

### Respuestas de Error

#### 403 Forbidden - Sin Acceso al Subinventario
```json
{
  "success": false,
  "message": "No tienes acceso a este punto de venta (subinventario)"
}
```

#### 422 Unprocessable Entity - Stock Insuficiente
```json
{
  "success": false,
  "message": "Cantidad insuficiente para 'Biblia Reina Valera 1960'. Disponible: 2"
}
```

#### 422 Unprocessable Entity - Libro No Disponible
```json
{
  "success": false,
  "message": "El libro 'Devocional Jesús Te Llama' no está en este subinventario"
}
```

#### 422 Unprocessable Entity - Cliente Requerido
```json
{
  "success": false,
  "message": "Las ventas a crédito requieren un cliente asignado"
}
```

#### 422 Unprocessable Entity - Validación de Campos
```json
{
  "message": "The libros field is required.",
  "errors": {
    "libros": [
      "Debes agregar al menos un libro"
    ]
  }
}
```

#### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Error al crear la venta: [descripción del error]"
}
```

---

## 🔐 Validaciones Automáticas

El endpoint realiza las siguientes validaciones:

1. ✅ **Acceso al Subinventario**: Verifica que el usuario tenga permiso para vender en ese punto
2. ✅ **Estado del Subinventario**: Solo permite ventas en subinventarios activos
3. ✅ **Existencia de Libros**: Verifica que todos los libros estén en el subinventario
4. ✅ **Stock Disponible**: Valida que haya suficiente cantidad de cada libro
5. ✅ **Cliente Obligatorio**: Para ventas a crédito, exige un cliente asignado
6. ✅ **Datos de Envío**: Si `tiene_envio=true`, valida campos relacionados
7. ✅ **Descuentos Válidos**: Los descuentos deben estar entre 0 y 100%

---

## 🔄 Proceso Interno

Cuando se crea una venta, el sistema automáticamente:

1. **Crea el registro de venta** con todos los datos
2. **Genera movimientos de salida** para cada libro vendido
3. **Actualiza el stock del subinventario** (reduce cantidades)
4. **Actualiza el stock general** de los libros
5. **Calcula totales** (subtotal, descuentos, envío, total)
6. **Marca el pago** (completado para contado, pendiente para crédito)
7. **Crea registro de envío** si `tiene_envio=true`
8. **Registra en logs** para auditoría

---

## 📱 Implementación en App Móvil

### Función Completa en React Native

```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE = 'https://inventario.sistemasdevida.com/api/v1';

/**
 * Crear venta desde app móvil
 * @param {Object} ventaData - Datos de la venta
 * @param {number} ventaData.subinventarioId - ID del punto de venta
 * @param {Array} ventaData.libros - Array de {libro_id, cantidad, descuento?}
 * @param {number} ventaData.clienteId - ID del cliente (opcional)
 * @param {string} ventaData.tipoPago - 'contado', 'credito', 'mixto'
 * @param {number} ventaData.descuentoGlobal - Descuento % (opcional)
 * @param {string} ventaData.observaciones - Notas (opcional)
 * @param {Object} ventaData.envio - Datos de envío (opcional)
 */
async function crearVenta(ventaData) {
  try {
    // Obtener datos del usuario
    const codCongregante = await AsyncStorage.getItem('codCongregante');
    const username = await AsyncStorage.getItem('username');
    
    if (!codCongregante || !username) {
      throw new Error('Usuario no autenticado');
    }
    
    // Preparar body del request
    const body = {
      subinventario_id: ventaData.subinventarioId,
      cod_congregante: codCongregante,
      fecha_venta: new Date().toISOString().split('T')[0],
      tipo_pago: ventaData.tipoPago || 'contado',
      usuario: username,
      libros: ventaData.libros,
    };
    
    // Agregar campos opcionales
    if (ventaData.clienteId) {
      body.cliente_id = ventaData.clienteId;
    }
    
    if (ventaData.descuentoGlobal) {
      body.descuento_global = ventaData.descuentoGlobal;
    }
    
    if (ventaData.observaciones) {
      body.observaciones = ventaData.observaciones;
    }
    
    // Agregar datos de envío si aplica
    if (ventaData.envio) {
      body.tiene_envio = true;
      body.costo_envio = ventaData.envio.costo;
      body.direccion_envio = ventaData.envio.direccion;
      body.telefono_envio = ventaData.envio.telefono;
    }
    
    // Hacer request
    const response = await fetch(`${API_BASE}/ventas`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(body),
    });
    
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Error al crear la venta');
    }
    
    return {
      success: true,
      venta: data.data,
    };
    
  } catch (error) {
    console.error('Error creando venta:', error);
    return {
      success: false,
      error: error.message,
    };
  }
}

// Ejemplo de uso
async function ejemplo() {
  const resultado = await crearVenta({
    subinventarioId: 1,
    tipoPago: 'contado',
    libros: [
      { libro_id: 12, cantidad: 2, descuento: 0 },
      { libro_id: 23, cantidad: 1, descuento: 5 },
    ],
    descuentoGlobal: 10,
    observaciones: 'Venta desde app móvil',
  });
  
  if (resultado.success) {
    alert(`Venta creada! ID: ${resultado.venta.venta_id}, Total: $${resultado.venta.total}`);
  } else {
    alert(`Error: ${resultado.error}`);
  }
}
```

---

## 🧪 Pruebas con cURL

### Prueba Básica
```bash
curl -X POST "https://inventario.sistemasdevida.com/api/v1/ventas" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "subinventario_id": 1,
    "cod_congregante": "14279",
    "fecha_venta": "2026-01-08",
    "tipo_pago": "contado",
    "usuario": "Test User",
    "libros": [
      {"libro_id": 180, "cantidad": 1}
    ]
  }'
```

### Prueba con Todos los Campos
```bash
curl -X POST "https://inventario.sistemasdevida.com/api/v1/ventas" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "subinventario_id": 1,
    "cod_congregante": "14279",
    "cliente_id": 1,
    "fecha_venta": "2026-01-08",
    "tipo_pago": "contado",
    "descuento_global": 10,
    "observaciones": "Venta de prueba completa",
    "usuario": "Test User",
    "tiene_envio": true,
    "costo_envio": 100,
    "direccion_envio": "Dirección de prueba",
    "telefono_envio": "809-555-0000",
    "libros": [
      {"libro_id": 180, "cantidad": 2, "descuento": 5}
    ]
  }'
```

---

## 📊 Códigos de Estado HTTP

| Código | Significado | Descripción |
|--------|-------------|-------------|
| 201 | Created | Venta creada exitosamente |
| 400 | Bad Request | Request malformado (JSON inválido) |
| 403 | Forbidden | Sin acceso al subinventario |
| 422 | Unprocessable Entity | Validación fallida (datos incorrectos) |
| 500 | Internal Server Error | Error del servidor |

---

## 🔍 Consultar Venta Creada

Después de crear una venta, puedes consultar los detalles en la aplicación web:

```
https://inventario.sistemasdevida.com/ventas/{venta_id}
```

---

## 📞 Solución de Problemas

### Error: "No tienes acceso a este punto de venta"
**Causa:** El `cod_congregante` no tiene permisos en el `subinventario_id`  
**Solución:** Verifica que el usuario esté asignado al subinventario en la tabla `subinventario_user`

### Error: "El libro no está en este subinventario"
**Causa:** El libro no pertenece al punto de venta seleccionado  
**Solución:** Carga los libros del subinventario antes de crear la venta (endpoint `/api/v1/subinventarios/{id}/libros`)

### Error: "Cantidad insuficiente"
**Causa:** No hay suficiente stock en el subinventario  
**Solución:** Verifica el stock disponible antes de permitir al usuario seleccionar la cantidad

### Error: "Las ventas a crédito requieren un cliente"
**Causa:** Falta el campo `cliente_id` en venta a crédito  
**Solución:** Solicita al usuario seleccionar un cliente antes de completar la venta

---

## 🔐 Seguridad

1. **Validación de Acceso**: Siempre envía `cod_congregante` para validar permisos
2. **Transacciones**: El sistema usa transacciones de base de datos para garantizar integridad
3. **Logging**: Todas las ventas se registran en logs para auditoría
4. **Rollback Automático**: Si hay error, se deshacen todos los cambios

---

## 📈 Mejores Prácticas

1. **Valida stock antes de enviar**: Evita errores mostrando solo libros disponibles
2. **Maneja errores gracefully**: Muestra mensajes claros al usuario
3. **Guarda offline**: Considera guardar ventas offline y sincronizar después
4. **Confirma al usuario**: Muestra un resumen antes de crear la venta
5. **Actualiza inventario**: Después de crear una venta, recarga el inventario del punto de venta

---

## 🆘 Soporte

Para reportar problemas o solicitar ayuda:
- Revisa los logs del servidor en `/storage/logs/laravel.log`
- Verifica la conexión a internet
- Confirma que el servidor esté disponible

---

## 📝 Changelog

### v1.0.0 (2026-01-08)
- ✅ Validación de acceso por `cod_congregante`
- ✅ Soporte para envíos
- ✅ Soporte para clientes
- ✅ Ventas a crédito
- ✅ Descuentos globales e individuales
- ✅ Actualización automática de stock
- ✅ Logging de auditoría
- ✅ Respuestas detalladas con todos los totales
