# 📱 API REST Completa - App Móvil Punto de Venta

**Guía completa y definitiva** para integrar tu aplicación móvil con el sistema de inventario.

---

## 📑 Tabla de Contenidos

1. [🌐 Configuración Inicial](#-configuración-inicial)
2. [🔐 Autenticación](#-autenticación)
3. [📍 Listar Puntos de Venta](#-1-listar-puntos-de-venta)
4. [📚 Cargar Inventario](#-2-cargar-inventario)
5. [💰 Crear Ventas](#-3-crear-ventas)
6. [📦 Crear Apartados](#-4-crear-apartados)
7. [🆚 Venta vs Apartado](#-venta-vs-apartado---cuándo-usar-cada-uno)
8. [👥 Gestión de Clientes](#-gestión-de-clientes)
9. [📱 Implementación React Native](#-implementación-completa-en-react-native)
10. [🧪 Pruebas y Testing](#-pruebas-con-curl)
11. [❓ Solución de Problemas](#-solución-de-problemas-comunes)
12. [🚀 Despliegue](#-despliegue-en-producción)

---

## 🌐 Configuración Inicial

### URLs del Sistema

**Producción:**
```
https://inventario.sistemasdevida.com
```

**API Base:**
```
https://inventario.sistemasdevida.com/api/v1
```

**Local (desarrollo):**
```
http://localhost:8000/api/v1
```

### Headers Requeridos

Todos los requests POST deben incluir:
```javascript
{
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
```

---

## 🔐 Autenticación

### Sistema Externo de Login

La autenticación se realiza contra un sistema externo que devuelve el `codCongregante` (token de usuario).

**Endpoint de Login:**
```
POST https://www.sistemasdevida.com/pan/rest2/index.php/app/login
```

**Request:**
```json
{
  "usuario": "nombre_usuario",
  "password": "contraseña"
}
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "codCongregante": "14279",
  "nombre": "Juan Pérez Gómez",
  "email": "juan@ejemplo.com"
}
```

### Guardar Token en App Móvil

```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

// Después del login exitoso
await AsyncStorage.setItem('codCongregante', response.codCongregante);
await AsyncStorage.setItem('username', response.nombre);
await AsyncStorage.setItem('email', response.email);
```

### Usar Token en Requests

El `codCongregante` se envía en cada request para validar acceso:

```javascript
const codCongregante = await AsyncStorage.getItem('codCongregante');

// En body de POST
{
  "cod_congregante": codCongregante,
  // ...otros campos
}

// O en query params de GET
?cod_congregante=${codCongregante}
```

---

## 📍 1. Listar Puntos de Venta

### Endpoint

```
GET /api/v1/mis-subinventarios/{cod_congregante}
```

### Descripción

Lista todos los puntos de venta (subinventarios) asignados al usuario. **No carga libros**, solo información general.

### Request

```bash
GET /api/v1/mis-subinventarios/14279
```

### Respuesta Exitosa (200)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Punto de Venta Central",
      "descripcion": "Librería principal",
      "fecha_subinventario": "2026-01-05",
      "estado": "activo",
      "total_libros": 27,
      "total_unidades": 79
    },
    {
      "id": 2,
      "nombre": "Punto de Venta Norte",
      "descripcion": "Sucursal norte",
      "fecha_subinventario": "2026-01-03",
      "estado": "activo",
      "total_libros": 15,
      "total_unidades": 42
    }
  ]
}
```

### Campos de Respuesta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | integer | ID del punto de venta |
| `nombre` | string | Nombre del punto |
| `descripcion` | string | Descripción |
| `fecha_subinventario` | date | Fecha de creación |
| `estado` | string | `activo` o `inactivo` |
| `total_libros` | integer | Cantidad de títulos diferentes |
| `total_unidades` | integer | Cantidad total de libros |

### Ejemplo React Native

```javascript
async function obtenerMisPuntosDeVenta() {
  try {
    const codCongregante = await AsyncStorage.getItem('codCongregante');
    
    const response = await fetch(
      `https://inventario.sistemasdevida.com/api/v1/mis-subinventarios/${codCongregante}`,
      {
        headers: {
          'Accept': 'application/json',
        },
      }
    );
    
    const data = await response.json();
    
    if (data.success) {
      return data.data; // Array de puntos de venta
    } else {
      throw new Error('Error al cargar puntos de venta');
    }
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}
```

---

## 📚 2. Cargar Inventario

### Endpoint

```
GET /api/v1/subinventarios/{id}/libros
```

### Descripción

Carga todos los libros disponibles en un punto de venta específico. Solo muestra libros con `stock > 0`.

### Parámetros

| Parámetro | Ubicación | Tipo | Obligatorio | Descripción |
|-----------|-----------|------|-------------|-------------|
| `id` | URL | integer | Sí | ID del subinventario |
| `cod_congregante` | Query | string | No | Token para validar acceso |

### Request

```bash
GET /api/v1/subinventarios/1/libros?cod_congregante=14279
```

### Respuesta Exitosa (200)

```json
{
  "success": true,
  "data": {
    "subinventario": {
      "id": 1,
      "nombre": "Punto de Venta Central",
      "descripcion": "Librería principal",
      "fecha_subinventario": "2026-01-05",
      "estado": "activo"
    },
    "total_libros": 27,
    "total_unidades": 79,
    "libros": [
      {
        "id": 12,
        "nombre": "Biblia Reina Valera 1960",
        "autor": "Varios",
        "editorial": "Vida",
        "codigo_barras": "9788408234567",
        "precio": 350.00,
        "stock_general": 50,
        "cantidad_disponible": 10
      },
      {
        "id": 23,
        "nombre": "Devocional Jesús Te Llama",
        "autor": "Sarah Young",
        "editorial": "Grupo Nelson",
        "codigo_barras": "9780789012345",
        "precio": 280.00,
        "stock_general": 30,
        "cantidad_disponible": 5
      }
    ]
  }
}
```

### Campos de Libro

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | integer | ID del libro |
| `nombre` | string | Título del libro |
| `autor` | string | Autor(es) |
| `editorial` | string | Editorial |
| `codigo_barras` | string | Código de barras |
| `precio` | decimal | Precio unitario |
| `stock_general` | integer | Stock total en inventario |
| `cantidad_disponible` | integer | Cantidad en este punto de venta |

### Ejemplo React Native

```javascript
async function cargarLibrosSubinventario(subinventarioId) {
  try {
    const codCongregante = await AsyncStorage.getItem('codCongregante');
    
    const response = await fetch(
      `https://inventario.sistemasdevida.com/api/v1/subinventarios/${subinventarioId}/libros?cod_congregante=${codCongregante}`,
      {
        headers: {
          'Accept': 'application/json',
        },
      }
    );
    
    const data = await response.json();
    
    if (data.success) {
      return data.data.libros;
    } else {
      throw new Error(data.message || 'Error al cargar libros');
    }
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}
```

---

## 💰 3. Crear Ventas

### Endpoint

```
POST /api/v1/ventas
```

### Descripción

Crea una venta con pago completo (contado/crédito/mixto). El stock se reduce inmediatamente.

### Parámetros Obligatorios

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `subinventario_id` | integer | ID del punto de venta |
| `cod_congregante` | string | Token del usuario |
| `fecha_venta` | date | Fecha de la venta (YYYY-MM-DD) |
| `tipo_pago` | string | `contado`, `credito`, o `mixto` |
| `usuario` | string | Nombre del usuario que vende |
| `libros` | array | Array de libros vendidos (mínimo 1) |
| `libros[].libro_id` | integer | ID del libro |
| `libros[].cantidad` | integer | Cantidad vendida (mínimo 1) |

### Parámetros Opcionales

| Campo | Tipo | Descripción | Default |
|-------|------|-------------|---------|
| `cliente_id` | integer | ID del cliente (obligatorio si `tipo_pago=credito`) | null |
| `descuento_global` | decimal | Descuento general (0-100%) | 0 |
| `observaciones` | string | Notas adicionales (máx 500 caracteres) | null |
| `libros[].descuento` | decimal | Descuento individual (0-100%) | 0 |
| `tiene_envio` | boolean | Si incluye envío | false |
| `costo_envio` | decimal | Costo del envío | 0 |
| `direccion_envio` | string | Dirección de entrega (máx 500 caracteres) | null |
| `telefono_envio` | string | Teléfono de contacto (máx 20 caracteres) | null |

### Tipos de Pago

#### 🟢 Contado (Pago Completo)

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
      "cantidad": 2,
      "descuento": 0
    }
  ]
}
```

#### 🔴 Crédito (Pago Diferido)

```json
{
  "subinventario_id": 1,
  "cod_congregante": "14279",
  "cliente_id": 5,
  "fecha_venta": "2026-01-08",
  "tipo_pago": "credito",
  "usuario": "Juan Pérez",
  "observaciones": "Pago en 3 cuotas mensuales",
  "libros": [
    {
      "libro_id": 12,
      "cantidad": 2
    }
  ]
}
```

⚠️ **Importante:** Para ventas a crédito, el campo `cliente_id` es **obligatorio**.

#### 🟡 Mixto (Anticipo + Saldo)

```json
{
  "subinventario_id": 1,
  "cod_congregante": "14279",
  "cliente_id": 5,
  "fecha_venta": "2026-01-08",
  "tipo_pago": "mixto",
  "usuario": "Juan Pérez",
  "libros": [
    {
      "libro_id": 12,
      "cantidad": 2
    }
  ]
}
```

### Ejemplo Completo con Todas las Opciones

```json
{
  "subinventario_id": 1,
  "cod_congregante": "14279",
  "cliente_id": 5,
  "fecha_venta": "2026-01-08",
  "tipo_pago": "contado",
  "descuento_global": 10,
  "observaciones": "Cliente frecuente - descuento especial",
  "usuario": "Juan Pérez",
  "tiene_envio": true,
  "costo_envio": 150.00,
  "direccion_envio": "Calle Principal #123, Santo Domingo",
  "telefono_envio": "809-555-1234",
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

### Respuesta Exitosa (201)

```json
{
  "success": true,
  "message": "Venta creada exitosamente",
  "data": {
    "venta_id": 262,
    "subtotal": "1180.00",
    "descuento": "118.00",
    "costo_envio": "150.00",
    "total": "1212.00",
    "total_pagado": "1212.00",
    "saldo_pendiente": "0.00",
    "estado_pago": "completado",
    "tiene_envio": true
  }
}
```

### Errores Comunes

#### 403 - Sin Acceso
```json
{
  "success": false,
  "message": "No tienes acceso a este punto de venta (subinventario)"
}
```

#### 422 - Stock Insuficiente
```json
{
  "success": false,
  "message": "Cantidad insuficiente para 'Biblia Reina Valera 1960'. Disponible: 2"
}
```

#### 422 - Cliente Requerido
```json
{
  "success": false,
  "message": "Las ventas a crédito requieren un cliente asignado"
}
```

### Ejemplo React Native

```javascript
async function crearVenta({
  subinventarioId,
  libros,
  tipoPago = 'contado',
  clienteId = null,
  descuentoGlobal = 0,
  observaciones = null,
  envio = null
}) {
  try {
    const codCongregante = await AsyncStorage.getItem('codCongregante');
    const username = await AsyncStorage.getItem('username');
    
    const body = {
      subinventario_id: subinventarioId,
      cod_congregante: codCongregante,
      fecha_venta: new Date().toISOString().split('T')[0],
      tipo_pago: tipoPago,
      usuario: username,
      libros: libros,
    };
    
    if (clienteId) body.cliente_id = clienteId;
    if (descuentoGlobal > 0) body.descuento_global = descuentoGlobal;
    if (observaciones) body.observaciones = observaciones;
    
    if (envio) {
      body.tiene_envio = true;
      body.costo_envio = envio.costo;
      body.direccion_envio = envio.direccion;
      body.telefono_envio = envio.telefono;
    }
    
    const response = await fetch(
      'https://inventario.sistemasdevida.com/api/v1/ventas',
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(body),
      }
    );
    
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Error al crear la venta');
    }
    
    return data.data;
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

// Uso
const resultado = await crearVenta({
  subinventarioId: 1,
  tipoPago: 'contado',
  libros: [
    { libro_id: 12, cantidad: 2, descuento: 5 },
    { libro_id: 23, cantidad: 1, descuento: 0 },
  ],
  descuentoGlobal: 10,
  observaciones: 'Venta desde app móvil',
});

alert(`Venta creada! ID: ${resultado.venta_id}, Total: $${resultado.total}`);
```

---

## 📦 4. Crear Apartados

### Endpoint

```
POST /api/v1/apartados
```

### Descripción

Crea un apartado (reserva con anticipo). El cliente paga un **enganche** y puede hacer **abonos** hasta completar el total. Los libros quedan reservados (`stock_apartado`).

### ¿Qué es un Apartado?

- Cliente reserva libros pagando un **enganche** (anticipo)
- Tiene plazo límite para liquidar
- Puede hacer **abonos parciales**
- Los libros quedan **separados** del inventario disponible
- Al liquidar, se convierte en **venta automáticamente**

### Parámetros Obligatorios

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `subinventario_id` | integer | ID del punto de venta |
| `cod_congregante` | string | Token del usuario |
| `cliente_id` | integer | ID del cliente (obligatorio) |
| `fecha_apartado` | date | Fecha del apartado (YYYY-MM-DD) |
| `enganche` | decimal | Monto del anticipo (mínimo 0) |
| `usuario` | string | Nombre del usuario que registra |
| `libros` | array | Array de libros apartados (mínimo 1) |
| `libros[].libro_id` | integer | ID del libro |
| `libros[].cantidad` | integer | Cantidad a apartar |
| `libros[].precio_unitario` | decimal | Precio unitario del libro |

### Parámetros Opcionales

| Campo | Tipo | Descripción | Default |
|-------|------|-------------|---------|
| `fecha_limite` | date | Fecha límite para liquidar (debe ser futura) | null |
| `observaciones` | string | Notas adicionales (máx 500 caracteres) | null |
| `libros[].descuento` | decimal | Descuento individual (0-100%) | 0 |

### Ejemplo Básico

```json
{
  "subinventario_id": 1,
  "cod_congregante": "14279",
  "cliente_id": 5,
  "fecha_apartado": "2026-01-08",
  "enganche": 500.00,
  "usuario": "Juan Pérez",
  "libros": [
    {
      "libro_id": 12,
      "cantidad": 2,
      "precio_unitario": 350.00
    }
  ]
}
```

**Cálculo:**
- Monto Total: 2 × $350 = **$700.00**
- Enganche: **$500.00**
- Saldo Pendiente: **$200.00**

### Ejemplo con Fecha Límite y Descuentos

```json
{
  "subinventario_id": 1,
  "cod_congregante": "14279",
  "cliente_id": 8,
  "fecha_apartado": "2026-01-08",
  "fecha_limite": "2026-01-15",
  "enganche": 300.00,
  "observaciones": "Cliente frecuente - 7 días para liquidar",
  "usuario": "María González",
  "libros": [
    {
      "libro_id": 180,
      "cantidad": 3,
      "precio_unitario": 250.00,
      "descuento": 10
    },
    {
      "libro_id": 156,
      "cantidad": 2,
      "precio_unitario": 400.00,
      "descuento": 5
    }
  ]
}
```

**Cálculo:**
- Libro 180: 3 × $250 × (1 - 10%) = **$675.00**
- Libro 156: 2 × $400 × (1 - 5%) = **$760.00**
- Monto Total: **$1,435.00**
- Enganche: **$300.00**
- Saldo Pendiente: **$1,135.00**

### Ejemplo Sin Enganche

```json
{
  "subinventario_id": 1,
  "cod_congregante": "14279",
  "cliente_id": 3,
  "fecha_apartado": "2026-01-08",
  "fecha_limite": "2026-01-10",
  "enganche": 0,
  "observaciones": "Apartado sin enganche - liquidar antes del 10 de enero",
  "usuario": "Pedro Martínez",
  "libros": [
    {
      "libro_id": 45,
      "cantidad": 1,
      "precio_unitario": 590.00
    }
  ]
}
```

✅ **Es válido** crear apartados con enganche $0.00

### Respuesta Exitosa (201)

```json
{
  "success": true,
  "message": "Apartado creado exitosamente",
  "data": {
    "apartado_id": 4,
    "folio": "AP-2026-0002",
    "monto_total": "1435.00",
    "enganche": "300.00",
    "saldo_pendiente": "1135.00",
    "estado": "activo",
    "fecha_apartado": "2026-01-08",
    "fecha_limite": "2026-01-15"
  }
}
```

### Estados del Apartado

| Estado | Descripción |
|--------|-------------|
| **activo** | Apartado vigente, esperando abonos/liquidación |
| **liquidado** | Pagado completamente, se convirtió en venta |
| **cancelado** | Cancelado, inventario devuelto al subinventario |

### Errores Comunes

#### 403 - Sin Acceso
```json
{
  "success": false,
  "message": "No tienes acceso a este punto de venta (subinventario)"
}
```

#### 422 - Stock Insuficiente
```json
{
  "success": false,
  "message": "Cantidad insuficiente para 'Biblia Reina Valera 1960'. Disponible: 2"
}
```

#### 422 - Enganche Mayor al Total
```json
{
  "success": false,
  "message": "El enganche no puede ser mayor al monto total del apartado"
}
```

#### 422 - Fecha Límite Inválida
```json
{
  "success": false,
  "message": "Datos inválidos",
  "errors": {
    "fecha_limite": ["La fecha límite debe ser posterior a hoy"]
  }
}
```

### Ejemplo React Native

```javascript
async function crearApartado({
  subinventarioId,
  clienteId,
  libros,
  enganche,
  fechaLimite = null,
  observaciones = null
}) {
  try {
    const codCongregante = await AsyncStorage.getItem('codCongregante');
    const username = await AsyncStorage.getItem('username');
    
    const body = {
      subinventario_id: subinventarioId,
      cod_congregante: codCongregante,
      cliente_id: clienteId,
      fecha_apartado: new Date().toISOString().split('T')[0],
      enganche: enganche,
      usuario: username,
      libros: libros,
    };
    
    if (fechaLimite) body.fecha_limite = fechaLimite;
    if (observaciones) body.observaciones = observaciones;
    
    const response = await fetch(
      'https://inventario.sistemasdevida.com/api/v1/apartados',
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(body),
      }
    );
    
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Error al crear el apartado');
    }
    
    return data.data;
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

// Uso
const resultado = await crearApartado({
  subinventarioId: 1,
  clienteId: 5,
  enganche: 500.00,
  fechaLimite: '2026-01-15', // 7 días
  libros: [
    { libro_id: 12, cantidad: 2, precio_unitario: 350.00, descuento: 10 },
    { libro_id: 23, cantidad: 1, precio_unitario: 480.00, descuento: 0 },
  ],
  observaciones: 'Cliente frecuente',
});

alert(`Apartado creado!\nFolio: ${resultado.folio}\nTotal: $${resultado.monto_total}\nEnganche: $${resultado.enganche}\nSaldo: $${resultado.saldo_pendiente}`);
```

---

## 🆚 Venta vs Apartado - Cuándo Usar Cada Uno

### Tabla Comparativa

| Característica | 💰 Venta | 📦 Apartado |
|----------------|----------|-------------|
| **Endpoint** | POST /api/v1/ventas | POST /api/v1/apartados |
| **Pago** | Completo o crédito | Enganche + abonos |
| **Cliente** | Opcional* | **Obligatorio** |
| **Entrega** | Inmediata | Al liquidar |
| **Stock** | Se reduce de inmediato | Se reserva (stock_apartado) |
| **Folio** | VEN-YYYY-NNNN | AP-YYYY-NNNN |
| **Abonos** | Solo si es a crédito | Siempre permite abonos |
| **Fecha Límite** | No aplica | Opcional |
| **Precio Unitario** | Se toma del sistema | Se especifica en request |

*Obligatorio solo para ventas a crédito

### Escenarios de Uso

#### Usar VENTA cuando:
- ✅ Cliente paga completo al momento
- ✅ Se entrega inmediatamente
- ✅ Venta al contado sin cliente específico
- ✅ Venta a crédito con cliente asignado

#### Usar APARTADO cuando:
- ✅ Cliente paga anticipo (enganche)
- ✅ Se entrega después de liquidar
- ✅ Cliente necesita plazo para completar pago
- ✅ Quieres reservar inventario para evento futuro
- ✅ Cliente hará abonos parciales

### Flujo de Decisión en App

```
Usuario selecciona libros
        ↓
¿Cliente pagará todo ahora?
        ↓
    SÍ → VENTA
        ↓
    ¿Paga ahora o después?
        ↓
    Ahora → tipo_pago: "contado"
    Después → tipo_pago: "credito" (requiere cliente_id)
    
    NO → APARTADO
        ↓
    ¿Cuánto pagará de enganche?
        ↓
    Ingresar monto
        ↓
    ¿Cuándo debe liquidar?
        ↓
    Seleccionar fecha_limite (opcional)
```

---

## 👥 Gestión de Clientes

### Endpoint

```
GET /api/v1/clientes
```

### Descripción

Lista todos los clientes registrados en el sistema. Usar para seleccionar cliente en ventas a crédito o apartados.

### Request

```bash
GET /api/v1/clientes
```

### Respuesta Exitosa (200)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "María García López",
      "telefono": "809-555-1234",
      "email": "maria@ejemplo.com",
      "direccion": "Calle Principal #123, Santo Domingo",
      "limite_credito": 5000.00,
      "saldo_pendiente": 1200.00
    },
    {
      "id": 2,
      "nombre": "Carlos Pérez Martínez",
      "telefono": "809-555-5678",
      "email": "carlos@ejemplo.com",
      "direccion": "Avenida Central #456, Santiago",
      "limite_credito": 3000.00,
      "saldo_pendiente": 0.00
    }
  ]
}
```

### Ejemplo React Native

```javascript
async function obtenerClientes() {
  try {
    const response = await fetch(
      'https://inventario.sistemasdevida.com/api/v1/clientes',
      {
        headers: {
          'Accept': 'application/json',
        },
      }
    );
    
    const data = await response.json();
    
    if (data.success) {
      return data.data;
    } else {
      throw new Error('Error al cargar clientes');
    }
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}
```

---

## 📱 Implementación Completa en React Native

### Estructura del Proyecto

```
src/
  services/
    api.js          # Configuración y funciones API
    auth.js         # Autenticación
  screens/
    LoginScreen.js
    PuntosVentaScreen.js
    InventarioScreen.js
    VentaScreen.js
    ApartadoScreen.js
  components/
    LibroItem.js
    ClienteSelector.js
```

### api.js - Servicio Completo

```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE = 'https://inventario.sistemasdevida.com/api/v1';

class ApiService {
  /**
   * Obtener puntos de venta del usuario
   */
  async getMisPuntosVenta() {
    try {
      const codCongregante = await AsyncStorage.getItem('codCongregante');
      
      const response = await fetch(
        `${API_BASE}/mis-subinventarios/${codCongregante}`,
        {
          headers: {
            'Accept': 'application/json',
          },
        }
      );
      
      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.message || 'Error al cargar puntos de venta');
      }
      
      return data.data;
    } catch (error) {
      console.error('Error en getMisPuntosVenta:', error);
      throw error;
    }
  }

  /**
   * Obtener libros de un punto de venta
   */
  async getLibrosSubinventario(subinventarioId) {
    try {
      const codCongregante = await AsyncStorage.getItem('codCongregante');
      
      const response = await fetch(
        `${API_BASE}/subinventarios/${subinventarioId}/libros?cod_congregante=${codCongregante}`,
        {
          headers: {
            'Accept': 'application/json',
          },
        }
      );
      
      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.message || 'Error al cargar libros');
      }
      
      return data.data.libros;
    } catch (error) {
      console.error('Error en getLibrosSubinventario:', error);
      throw error;
    }
  }

  /**
   * Obtener lista de clientes
   */
  async getClientes() {
    try {
      const response = await fetch(
        `${API_BASE}/clientes`,
        {
          headers: {
            'Accept': 'application/json',
          },
        }
      );
      
      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.message || 'Error al cargar clientes');
      }
      
      return data.data;
    } catch (error) {
      console.error('Error en getClientes:', error);
      throw error;
    }
  }

  /**
   * Crear venta
   */
  async crearVenta({
    subinventarioId,
    libros,
    tipoPago = 'contado',
    clienteId = null,
    descuentoGlobal = 0,
    observaciones = null,
    envio = null
  }) {
    try {
      const codCongregante = await AsyncStorage.getItem('codCongregante');
      const username = await AsyncStorage.getItem('username');
      
      const body = {
        subinventario_id: subinventarioId,
        cod_congregante: codCongregante,
        fecha_venta: new Date().toISOString().split('T')[0],
        tipo_pago: tipoPago,
        usuario: username,
        libros: libros,
      };
      
      if (clienteId) body.cliente_id = clienteId;
      if (descuentoGlobal > 0) body.descuento_global = descuentoGlobal;
      if (observaciones) body.observaciones = observaciones;
      
      if (envio) {
        body.tiene_envio = true;
        body.costo_envio = envio.costo;
        body.direccion_envio = envio.direccion;
        body.telefono_envio = envio.telefono;
      }
      
      const response = await fetch(
        `${API_BASE}/ventas`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify(body),
        }
      );
      
      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.message || 'Error al crear la venta');
      }
      
      return data.data;
    } catch (error) {
      console.error('Error en crearVenta:', error);
      throw error;
    }
  }

  /**
   * Crear apartado
   */
  async crearApartado({
    subinventarioId,
    clienteId,
    libros,
    enganche,
    fechaLimite = null,
    observaciones = null
  }) {
    try {
      const codCongregante = await AsyncStorage.getItem('codCongregante');
      const username = await AsyncStorage.getItem('username');
      
      const body = {
        subinventario_id: subinventarioId,
        cod_congregante: codCongregante,
        cliente_id: clienteId,
        fecha_apartado: new Date().toISOString().split('T')[0],
        enganche: enganche,
        usuario: username,
        libros: libros,
      };
      
      if (fechaLimite) body.fecha_limite = fechaLimite;
      if (observaciones) body.observaciones = observaciones;
      
      const response = await fetch(
        `${API_BASE}/apartados`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify(body),
        }
      );
      
      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.message || 'Error al crear el apartado');
      }
      
      return data.data;
    } catch (error) {
      console.error('Error en crearApartado:', error);
      throw error;
    }
  }
}

export default new ApiService();
```

### Ejemplo de Pantalla de Venta

```javascript
import React, { useState, useEffect } from 'react';
import { View, Text, Button, Alert } from 'react-native';
import ApiService from '../services/api';

export default function VentaScreen({ route }) {
  const { subinventarioId } = route.params;
  const [libros, setLibros] = useState([]);
  const [carrito, setCarrito] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    cargarLibros();
  }, []);

  async function cargarLibros() {
    try {
      setLoading(true);
      const data = await ApiService.getLibrosSubinventario(subinventarioId);
      setLibros(data);
    } catch (error) {
      Alert.alert('Error', error.message);
    } finally {
      setLoading(false);
    }
  }

  async function procesarVenta() {
    try {
      if (carrito.length === 0) {
        Alert.alert('Error', 'Debes agregar al menos un libro');
        return;
      }

      setLoading(true);

      const resultado = await ApiService.crearVenta({
        subinventarioId: subinventarioId,
        libros: carrito,
        tipoPago: 'contado',
      });

      Alert.alert(
        'Venta Exitosa',
        `Venta ID: ${resultado.venta_id}\nTotal: $${resultado.total}`,
        [
          {
            text: 'OK',
            onPress: () => {
              setCarrito([]);
              cargarLibros(); // Recargar inventario
            },
          },
        ]
      );
    } catch (error) {
      Alert.alert('Error', error.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <View style={{ flex: 1, padding: 20 }}>
      <Text style={{ fontSize: 24, fontWeight: 'bold' }}>Crear Venta</Text>
      
      {/* Lista de libros y carrito aquí */}
      
      <Button
        title="Procesar Venta"
        onPress={procesarVenta}
        disabled={loading || carrito.length === 0}
      />
    </View>
  );
}
```

---

## 🧪 Pruebas con cURL

### 1. Listar Puntos de Venta

```bash
curl -X GET "https://inventario.sistemasdevida.com/api/v1/mis-subinventarios/14279" \
  -H "Accept: application/json"
```

### 2. Cargar Libros

```bash
curl -X GET "https://inventario.sistemasdevida.com/api/v1/subinventarios/1/libros?cod_congregante=14279" \
  -H "Accept: application/json"
```

### 3. Crear Venta Simple

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
      {
        "libro_id": 180,
        "cantidad": 1
      }
    ]
  }'
```

### 4. Crear Venta Completa

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
    "direccion_envio": "Calle Prueba #123",
    "telefono_envio": "809-555-1234",
    "libros": [
      {
        "libro_id": 180,
        "cantidad": 3,
        "descuento": 5
      }
    ]
  }'
```

### 5. Crear Apartado Básico

```bash
curl -X POST "https://inventario.sistemasdevida.com/api/v1/apartados" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "subinventario_id": 1,
    "cod_congregante": "14279",
    "cliente_id": 1,
    "fecha_apartado": "2026-01-08",
    "enganche": 200.00,
    "usuario": "Test User",
    "libros": [
      {
        "libro_id": 180,
        "cantidad": 2,
        "precio_unitario": 250.00
      }
    ]
  }'
```

### 6. Crear Apartado Completo

```bash
curl -X POST "https://inventario.sistemasdevida.com/api/v1/apartados" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "subinventario_id": 1,
    "cod_congregante": "14279",
    "cliente_id": 1,
    "fecha_apartado": "2026-01-08",
    "fecha_limite": "2026-01-15",
    "enganche": 500.00,
    "observaciones": "Apartado de prueba completo",
    "usuario": "Test User",
    "libros": [
      {
        "libro_id": 180,
        "cantidad": 3,
        "precio_unitario": 250.00,
        "descuento": 10
      },
      {
        "libro_id": 156,
        "cantidad": 2,
        "precio_unitario": 400.00,
        "descuento": 5
      }
    ]
  }'
```

### 7. Listar Clientes

```bash
curl -X GET "https://inventario.sistemasdevida.com/api/v1/clientes" \
  -H "Accept: application/json"
```

---

## ❓ Solución de Problemas Comunes

### Error 403: "No tienes acceso a este punto de venta"

**Causa:** El `cod_congregante` no tiene permisos en ese subinventario.

**Solución:**
1. Verificar que el usuario esté asignado al subinventario en la tabla `subinventario_user`
2. Confirmar que el `cod_congregante` sea correcto
3. En desarrollo, crear el registro manualmente:

```sql
INSERT INTO subinventario_user (subinventario_id, cod_congregante, nombre_congregante)
VALUES (1, '14279', 'Juan Pérez');
```

---

### Error 422: "Stock insuficiente"

**Causa:** No hay suficiente cantidad del libro en el subinventario.

**Solución:**
1. Cargar libros antes de mostrar al usuario: `GET /api/v1/subinventarios/{id}/libros`
2. Mostrar solo la `cantidad_disponible` de cada libro
3. Validar en frontend antes de enviar:

```javascript
if (cantidadSeleccionada > libro.cantidad_disponible) {
  Alert.alert('Error', `Solo hay ${libro.cantidad_disponible} disponibles`);
  return;
}
```

---

### Error 422: "El libro no está en este subinventario"

**Causa:** Intentas vender un libro que no pertenece a ese punto de venta.

**Solución:**
1. Cargar siempre los libros del subinventario específico
2. Validar que `libro_id` esté en la lista cargada
3. No permitir agregar libros que no estén en el inventario actual

---

### Error 422: "Las ventas a crédito requieren un cliente"

**Causa:** Falta el campo `cliente_id` en venta con `tipo_pago: "credito"`.

**Solución:**
```javascript
if (tipoPago === 'credito' && !clienteId) {
  Alert.alert('Error', 'Debes seleccionar un cliente para ventas a crédito');
  return;
}
```

---

### Error 422: "El enganche no puede ser mayor al monto total"

**Causa:** En apartado, el enganche es mayor que el total calculado.

**Solución:**
```javascript
const montoTotal = calcularTotal(libros);

if (enganche > montoTotal) {
  Alert.alert('Error', `El enganche máximo es $${montoTotal.toFixed(2)}`);
  return;
}
```

---

### Error 500: Error del servidor

**Causa:** Error interno en el servidor (base de datos, lógica, etc).

**Solución:**
1. Revisar logs del servidor: `/storage/logs/laravel.log`
2. Verificar conexión a base de datos
3. Confirmar que todos los campos requeridos estén presentes
4. Contactar al administrador del sistema

---

### Error: "Network request failed"

**Causa:** Problemas de conexión o servidor no disponible.

**Solución:**
1. Verificar conexión a internet
2. Confirmar que la URL sea correcta
3. Verificar que el servidor esté operativo:

```bash
curl https://inventario.sistemasdevida.com/api/v1/clientes
```

---

### Token Expirado o Inválido

**Síntoma:** Rechazo constante con error 403.

**Solución:**
1. Hacer login nuevamente
2. Actualizar el `codCongregante` en AsyncStorage
3. Implementar refresh automático:

```javascript
async function refreshLogin() {
  const response = await fetch('https://sistemasdevida.com/.../app/login', {
    method: 'POST',
    body: JSON.stringify({ usuario, password }),
  });
  
  const data = await response.json();
  await AsyncStorage.setItem('codCongregante', data.codCongregante);
}
```

---

## 🚀 Despliegue en Producción

### Requisitos Previos

- Servidor con PHP 8.2+
- MySQL 8.0+
- Git instalado
- Composer instalado

### Paso 1: Commit y Push

```bash
# En tu máquina local
cd /Users/usuario/Desktop/pan_control_interno

git add .
git commit -m "Add API REST para app móvil: ventas y apartados completos"
git push origin main
```

### Paso 2: Actualizar Servidor

```bash
# Conectar al servidor
ssh usuario@inventario.sistemasdevida.com

# Ir al directorio del proyecto
cd /path/to/pan_control_interno

# Actualizar código
git pull origin main

# Limpiar cachés
php artisan route:clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Opcional: optimizar para producción
php artisan config:cache
php artisan route:cache
```

### Paso 3: Verificar Endpoints

```bash
# Probar endpoint de puntos de venta
curl https://inventario.sistemasdevida.com/api/v1/mis-subinventarios/14279

# Probar endpoint de libros
curl https://inventario.sistemasdevida.com/api/v1/subinventarios/1/libros

# Probar endpoint de clientes
curl https://inventario.sistemasdevida.com/api/v1/clientes
```

### Paso 4: Crear Datos de Prueba

```sql
-- Asignar usuario a subinventario
INSERT INTO subinventario_user (subinventario_id, cod_congregante, nombre_congregante)
VALUES (1, '14279', 'Usuario Prueba');

-- Verificar
SELECT * FROM subinventario_user WHERE cod_congregante = '14279';
```

### Paso 5: Prueba Completa

```bash
# Crear venta de prueba
curl -X POST "https://inventario.sistemasdevida.com/api/v1/ventas" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "subinventario_id": 1,
    "cod_congregante": "14279",
    "fecha_venta": "2026-01-08",
    "tipo_pago": "contado",
    "usuario": "Test Production",
    "libros": [{"libro_id": 1, "cantidad": 1}]
  }'
```

### Monitoreo

Revisar logs regularmente:

```bash
# Ver últimas líneas del log
tail -n 100 /path/to/pan_control_interno/storage/logs/laravel.log

# Seguir log en tiempo real
tail -f /path/to/pan_control_interno/storage/logs/laravel.log
```

---

## 📊 Resumen de Endpoints

| Método | Endpoint | Descripción | Cliente | Enganche |
|--------|----------|-------------|---------|----------|
| GET | `/mis-subinventarios/{cod}` | Lista puntos de venta del usuario | - | - |
| GET | `/subinventarios/{id}/libros` | Carga libros de un punto | - | - |
| GET | `/clientes` | Lista clientes | - | - |
| POST | `/ventas` | Crea venta (pago completo) | Opcional* | - |
| POST | `/apartados` | Crea apartado (con anticipo) | **Obligatorio** | **Obligatorio** |

*Cliente obligatorio solo para ventas a crédito

---

## 🎯 Flujo Completo de Uso

```
┌─────────────────────────────────────────────────────┐
│ 1. LOGIN EN SISTEMA EXTERNO                        │
│    POST sistemasdevida.com/.../app/login            │
│    → Guardar codCongregante en AsyncStorage         │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ 2. LISTAR PUNTOS DE VENTA                           │
│    GET /api/v1/mis-subinventarios/{cod}             │
│    → Mostrar lista de puntos asignados              │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ 3. USUARIO SELECCIONA PUNTO DE VENTA                │
│    → Guardar subinventario_id seleccionado          │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ 4. CARGAR INVENTARIO                                │
│    GET /api/v1/subinventarios/{id}/libros           │
│    → Mostrar catálogo con precios y stock           │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ 5. USUARIO AGREGA LIBROS AL CARRITO                 │
│    → Validar stock disponible                       │
│    → Calcular subtotales                            │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ 6. USUARIO DECIDE TIPO DE TRANSACCIÓN               │
└─────────────┬───────────────────────────┬───────────┘
              │                           │
              ▼                           ▼
    ┌──────────────────┐      ┌──────────────────┐
    │ 💰 VENTA         │      │ 📦 APARTADO      │
    │ Pago completo    │      │ Pago con enganche│
    └─────────┬────────┘      └─────────┬────────┘
              │                          │
              ▼                          ▼
    ┌──────────────────┐      ┌──────────────────┐
    │ ¿Tipo de pago?   │      │ Seleccionar      │
    │ • Contado        │      │ cliente          │
    │ • Crédito        │      │ (obligatorio)    │
    │ • Mixto          │      └─────────┬────────┘
    └─────────┬────────┘                │
              │                          ▼
              │              ┌──────────────────┐
              │              │ Ingresar enganche│
              │              │ y fecha límite   │
              │              └─────────┬────────┘
              │                        │
              ▼                        ▼
    ┌──────────────────┐      ┌──────────────────┐
    │ POST /ventas     │      │ POST /apartados  │
    └─────────┬────────┘      └─────────┬────────┘
              │                          │
              └──────────┬───────────────┘
                         ▼
              ┌──────────────────┐
              │ MOSTRAR RESULTADO│
              │ • Folio          │
              │ • Total          │
              │ • Saldo          │
              └──────────────────┘
```

---

## ✅ Checklist de Implementación

### Backend
- ✅ Endpoint de puntos de venta implementado
- ✅ Endpoint de libros implementado
- ✅ Endpoint de ventas implementado
- ✅ Endpoint de apartados implementado
- ✅ Endpoint de clientes implementado
- ✅ Validación de acceso por cod_congregante
- ✅ Validación de stock disponible
- ✅ Transacciones de base de datos
- ✅ Logging de auditoría
- ✅ Manejo de errores completo

### App Móvil
- [ ] Pantalla de login con sistema externo
- [ ] Guardar token en AsyncStorage
- [ ] Pantalla de selección de punto de venta
- [ ] Pantalla de catálogo de libros
- [ ] Carrito de compras
- [ ] Pantalla de venta (contado/crédito)
- [ ] Pantalla de apartado (con enganche)
- [ ] Selector de clientes
- [ ] Validación de stock antes de agregar
- [ ] Cálculo de totales con descuentos
- [ ] Manejo de errores y mensajes al usuario
- [ ] Recarga de inventario después de venta

---

## 📝 Notas Finales

### Importantes
1. **Siempre validar stock** antes de permitir agregar al carrito
2. **Cliente obligatorio** para crédito y apartados
3. **Enganche puede ser $0** en apartados
4. **Fecha límite** es opcional en apartados
5. **Recargar inventario** después de cada transacción

### Mejores Prácticas
1. Mostrar resumen antes de confirmar
2. Confirmar con el usuario antes de enviar
3. Guardar borradores localmente (offline-first)
4. Sincronizar cuando haya conexión
5. Mostrar mensajes claros al usuario
6. Manejar errores gracefully

### Seguridad
1. Nunca exponer el token en logs
2. Validar siempre en backend
3. No confiar en validaciones del frontend
4. Usar HTTPS en producción
5. Implementar timeout en requests

---

## 📞 Soporte Técnico

**Logs del Servidor:**
```bash
/storage/logs/laravel.log
```

**Panel Web:**
```
https://inventario.sistemasdevida.com
```

**Documentación de Referencia:**
- Este documento (README_API_COMPLETO.md)

---

**Versión:** 1.0.0  
**Fecha:** 8 de enero de 2026  
**Sistema:** Pan Control Interno - API REST

© 2026 Sistema de Inventario - Todos los derechos reservados
