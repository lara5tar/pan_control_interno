# API de Apartados - App Móvil

## 📱 Crear Apartado desde Punto de Venta Móvil

Este documento explica cómo crear apartados (reservas con anticipo) desde tu aplicación móvil usando el endpoint de API.

---

## 🌐 Endpoint

```
POST /api/v1/apartados
```

**URL Completa:**
```
https://inventario.sistemasdevida.com/api/v1/apartados
```

---

## 📋 ¿Qué es un Apartado?

Un **apartado** es una modalidad de venta donde:
- El cliente reserva uno o varios libros
- Paga un **enganche** (anticipo inicial)
- Tiene un plazo límite para liquidar el saldo pendiente
- Los libros quedan separados del inventario disponible
- Puede hacer **abonos** hasta completar el total

**Diferencias con Venta:**
- **Venta**: Pago completo, entrega inmediata
- **Apartado**: Pago parcial, entrega al liquidar

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
| `cliente_id` | integer | ID del cliente que aparta |
| `fecha_apartado` | date | Fecha del apartado (formato: YYYY-MM-DD) |
| `enganche` | decimal | Monto del anticipo/enganche (mínimo 0) |
| `usuario` | string | Nombre del usuario que registra |
| `libros` | array | Array de libros apartados (mínimo 1) |
| `libros[].libro_id` | integer | ID del libro |
| `libros[].cantidad` | integer | Cantidad a apartar (mínimo 1) |
| `libros[].precio_unitario` | decimal | Precio unitario del libro |

#### Campos Opcionales

| Campo | Tipo | Descripción | Default |
|-------|------|-------------|---------|
| `fecha_limite` | date | Fecha límite para liquidar (debe ser futura) | null |
| `observaciones` | string | Notas adicionales (máx 500 caracteres) | null |
| `libros[].descuento` | decimal | Descuento individual (0-100%) | 0 |

---

## 📝 Ejemplos de Uso

### Ejemplo 1: Apartado Simple

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
    },
    {
      "libro_id": 23,
      "cantidad": 1,
      "precio_unitario": 480.00
    }
  ]
}
```

**Resultado:**
- Monto Total: $1,180.00 (2×$350 + 1×$480)
- Enganche: $500.00
- Saldo Pendiente: $680.00

---

### Ejemplo 2: Apartado con Fecha Límite y Descuentos

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
- Libro 180: 3 × $250 × (1 - 10%) = $675.00
- Libro 156: 2 × $400 × (1 - 5%) = $760.00
- **Monto Total: $1,435.00**
- Enganche: $300.00
- **Saldo Pendiente: $1,135.00**

---

### Ejemplo 3: Apartado Sin Enganche (Enganche = 0)

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

**Nota:** ✅ Es válido crear un apartado con enganche $0.00 (el cliente puede pagar todo después).

---

## ✅ Respuesta Exitosa (201 Created)

```json
{
  "success": true,
  "message": "Apartado creado exitosamente",
  "data": {
    "apartado_id": 25,
    "folio": "AP-2026-0025",
    "monto_total": "1435.00",
    "enganche": "300.00",
    "saldo_pendiente": "1135.00",
    "estado": "activo",
    "fecha_apartado": "2026-01-08",
    "fecha_limite": "2026-01-15"
  }
}
```

---

## ❌ Respuestas de Error

### 403 Forbidden - Sin Acceso al Subinventario
```json
{
  "success": false,
  "message": "No tienes acceso a este punto de venta (subinventario)"
}
```

### 422 Unprocessable Entity - Stock Insuficiente
```json
{
  "success": false,
  "message": "Cantidad insuficiente para 'Biblia Reina Valera 1960'. Disponible: 2"
}
```

### 422 Unprocessable Entity - Libro No en Subinventario
```json
{
  "success": false,
  "message": "El libro 'Devocional Jesús Te Llama' no está en este subinventario"
}
```

### 422 Unprocessable Entity - Enganche Mayor al Total
```json
{
  "success": false,
  "message": "El enganche no puede ser mayor al monto total del apartado"
}
```

### 422 Unprocessable Entity - Validación de Campos
```json
{
  "success": false,
  "message": "Datos inválidos",
  "errors": {
    "cliente_id": ["Debe seleccionar un cliente"],
    "fecha_limite": ["La fecha límite debe ser posterior a hoy"],
    "libros": ["Debe agregar al menos un libro"]
  }
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Error al crear el apartado: [descripción del error]"
}
```

---

## 🔐 Validaciones Automáticas

El endpoint realiza las siguientes validaciones:

1. ✅ **Acceso al Subinventario**: Verifica permisos del usuario
2. ✅ **Estado del Subinventario**: Solo permite apartados en subinventarios activos
3. ✅ **Existencia de Libros**: Verifica que los libros estén en el subinventario
4. ✅ **Stock Disponible**: Valida suficiente cantidad en el subinventario
5. ✅ **Enganche Válido**: No puede ser mayor al monto total
6. ✅ **Fecha Límite**: Debe ser posterior a la fecha actual
7. ✅ **Cliente Válido**: El cliente debe existir en la base de datos

---

## 🔄 Proceso Interno

Cuando se crea un apartado, el sistema automáticamente:

1. **Genera folio único** (formato: AP-YYYY-NNNN)
2. **Calcula el monto total** con descuentos aplicados
3. **Calcula el saldo pendiente** (monto_total - enganche)
4. **Crea el registro de apartado** con estado "activo"
5. **Crea los detalles** (apartado_detalles) por cada libro
6. **Incrementa stock_apartado** de cada libro
7. **Decrementa cantidad en subinventario** (libros quedan reservados)
8. **Crea el primer abono** si hubo enganche > 0
9. **Registra en logs** para auditoría

---

## 📊 Estados de Apartado

| Estado | Descripción |
|--------|-------------|
| **activo** | Apartado vigente, esperando abonos/liquidación |
| **liquidado** | Pagado completamente, se convierte en venta |
| **cancelado** | Cancelado, inventario devuelto al subinventario |

**Nota:** Los apartados se crean siempre con estado **activo**.

---

## 💰 Gestión de Abonos

Después de crear el apartado, el cliente puede hacer abonos:
- Los abonos se registran en la tabla `abonos`
- Reducen el `saldo_pendiente` del apartado
- Cuando `saldo_pendiente = 0`, el apartado se puede **liquidar**
- Al liquidar, se crea una **venta** automáticamente

**Endpoint para abonos** (futuro):
```
POST /api/v1/apartados/{id}/abonos
```

---

## 📱 Implementación en App Móvil

### Función Completa en React Native

```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE = 'https://inventario.sistemasdevida.com/api/v1';

/**
 * Crear apartado desde app móvil
 * @param {Object} apartadoData - Datos del apartado
 * @param {number} apartadoData.subinventarioId - ID del punto de venta
 * @param {number} apartadoData.clienteId - ID del cliente
 * @param {Array} apartadoData.libros - Array de {libro_id, cantidad, precio_unitario, descuento?}
 * @param {number} apartadoData.enganche - Monto del anticipo
 * @param {string} apartadoData.fechaLimite - Fecha límite (YYYY-MM-DD) (opcional)
 * @param {string} apartadoData.observaciones - Notas (opcional)
 */
async function crearApartado(apartadoData) {
  try {
    // Obtener datos del usuario
    const codCongregante = await AsyncStorage.getItem('codCongregante');
    const username = await AsyncStorage.getItem('username');
    
    if (!codCongregante || !username) {
      throw new Error('Usuario no autenticado');
    }
    
    // Preparar body del request
    const body = {
      subinventario_id: apartadoData.subinventarioId,
      cod_congregante: codCongregante,
      cliente_id: apartadoData.clienteId,
      fecha_apartado: new Date().toISOString().split('T')[0],
      enganche: apartadoData.enganche,
      usuario: username,
      libros: apartadoData.libros,
    };
    
    // Agregar campos opcionales
    if (apartadoData.fechaLimite) {
      body.fecha_limite = apartadoData.fechaLimite;
    }
    
    if (apartadoData.observaciones) {
      body.observaciones = apartadoData.observaciones;
    }
    
    // Hacer request
    const response = await fetch(`${API_BASE}/apartados`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(body),
    });
    
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Error al crear el apartado');
    }
    
    return {
      success: true,
      apartado: data.data,
    };
    
  } catch (error) {
    console.error('Error creando apartado:', error);
    return {
      success: false,
      error: error.message,
    };
  }
}

// Ejemplo de uso
async function ejemploApartado() {
  const resultado = await crearApartado({
    subinventarioId: 1,
    clienteId: 5,
    enganche: 500.00,
    fechaLimite: '2026-01-15', // 7 días para liquidar
    libros: [
      { libro_id: 12, cantidad: 2, precio_unitario: 350.00, descuento: 0 },
      { libro_id: 23, cantidad: 1, precio_unitario: 480.00, descuento: 10 },
    ],
    observaciones: 'Cliente frecuente',
  });
  
  if (resultado.success) {
    const apt = resultado.apartado;
    alert(`Apartado creado!\nFolio: ${apt.folio}\nTotal: $${apt.monto_total}\nEnganche: $${apt.enganche}\nSaldo: $${apt.saldo_pendiente}`);
  } else {
    alert(`Error: ${resultado.error}`);
  }
}
```

---

## 🧪 Pruebas con cURL

### Prueba Básica

```bash
curl -X POST "http://localhost:8000/api/v1/apartados" \
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

### Prueba con Fecha Límite y Descuentos

```bash
curl -X POST "http://localhost:8000/api/v1/apartados" \
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

---

## 🆚 Comparación: Venta vs Apartado

| Característica | Venta | Apartado |
|----------------|-------|----------|
| **Endpoint** | POST /api/v1/ventas | POST /api/v1/apartados |
| **Pago** | Completo o crédito | Enganche + abonos |
| **Cliente** | Opcional (obligatorio si crédito) | **Obligatorio** |
| **Entrega** | Inmediata | Al liquidar |
| **Stock** | Se reduce de inmediato | Se reserva (stock_apartado) |
| **Estado final** | Venta registrada | Apartado activo → liquidar después |
| **Abonos** | Solo si es a crédito | Siempre permite abonos |

---

## 📞 Solución de Problemas

### Error: "No tienes acceso a este punto de venta"
**Causa:** El `cod_congregante` no tiene permisos en el `subinventario_id`  
**Solución:** Verificar asignación en tabla `subinventario_user`

### Error: "El libro no está en este subinventario"
**Causa:** El libro no pertenece al punto de venta seleccionado  
**Solución:** Cargar libros del subinventario antes (endpoint `/api/v1/subinventarios/{id}/libros`)

### Error: "Cantidad insuficiente"
**Causa:** No hay suficiente stock en el subinventario  
**Solución:** Mostrar solo la cantidad disponible al usuario

### Error: "El enganche no puede ser mayor al monto total"
**Causa:** Se ingresó un enganche superior al total del apartado  
**Solución:** Validar en frontend: `enganche <= monto_total_calculado`

### Error: "La fecha límite debe ser posterior a hoy"
**Causa:** Se proporcionó una fecha límite pasada o de hoy  
**Solución:** Validar: `fecha_limite > fecha_actual`

---

## 🔐 Seguridad

1. **Validación de Acceso**: Siempre envía `cod_congregante` para validar permisos
2. **Transacciones**: El sistema usa transacciones de base de datos para garantizar integridad
3. **Logging**: Todos los apartados se registran en logs para auditoría
4. **Rollback Automático**: Si hay error, se deshacen todos los cambios
5. **Folio Único**: Genera folios automáticos para evitar duplicados

---

## 📈 Mejores Prácticas

1. **Calcula el total antes de enviar**: Muestra al usuario el monto total antes de confirmar
2. **Valida el enganche**: El enganche debe ser ≤ monto total
3. **Sugiere fecha límite**: Ofrece opciones (7 días, 15 días, 30 días)
4. **Maneja errores gracefully**: Muestra mensajes claros al usuario
5. **Confirma antes de enviar**: Muestra un resumen del apartado
6. **Actualiza inventario**: Después de crear, recarga los libros del subinventario

---

## 🔗 Flujo Completo en App Móvil

```
1. Usuario selecciona "Apartado" en punto de venta
   ↓
2. Selecciona cliente (obligatorio)
   ↓
3. Agrega libros al carrito
   ↓
4. Sistema calcula monto total con descuentos
   ↓
5. Usuario ingresa enganche y fecha límite (opcional)
   ↓
6. Muestra resumen:
   - Monto Total: $X,XXX.XX
   - Enganche: $XXX.XX
   - Saldo Pendiente: $X,XXX.XX
   - Fecha Límite: DD/MM/YYYY
   ↓
7. Usuario confirma → POST /api/v1/apartados
   ↓
8. Sistema responde con folio y detalles
   ↓
9. App muestra confirmación con folio
   ↓
10. Actualiza inventario del subinventario
```

---

## 📊 Códigos de Estado HTTP

| Código | Significado | Descripción |
|--------|-------------|-------------|
| 201 | Created | Apartado creado exitosamente |
| 400 | Bad Request | Request malformado (JSON inválido) |
| 403 | Forbidden | Sin acceso al subinventario |
| 422 | Unprocessable Entity | Validación fallida (datos incorrectos) |
| 500 | Internal Server Error | Error del servidor |

---

## 🔍 Consultar Apartado Creado

Después de crear un apartado, puedes consultar los detalles en la aplicación web:

```
https://inventario.sistemasdevida.com/apartados/{apartado_id}
```

---

## 📝 Changelog

### v1.0.0 (2026-01-08)
- ✅ Validación de acceso por `cod_congregante`
- ✅ Creación de apartados con enganche
- ✅ Soporte para fecha límite
- ✅ Descuentos individuales por libro
- ✅ Validación de stock en subinventario
- ✅ Reserva automática de stock (stock_apartado)
- ✅ Primer abono automático si enganche > 0
- ✅ Logging de auditoría
- ✅ Respuestas detalladas con folio y totales

---

## 🆘 Soporte

Para reportar problemas o solicitar ayuda:
- Revisa los logs del servidor en `/storage/logs/laravel.log`
- Verifica la conexión a internet
- Confirma que el servidor esté disponible
- Consulta la documentación de ventas: `API_VENTAS_APP_MOVIL.md`
