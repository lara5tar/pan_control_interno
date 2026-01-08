# 📱 API REST - App Móvil Punto de Venta

Sistema completo de API REST para integración con aplicación móvil de punto de venta.

---

## 📚 Documentación

### 🚀 Inicio Rápido
- **[API_ENDPOINTS_RESUMEN.md](API_ENDPOINTS_RESUMEN.md)** - Resumen ejecutivo de todos los endpoints

### 📖 Guías Detalladas
1. **[INTEGRACION_APP_MOVIL.md](INTEGRACION_APP_MOVIL.md)** - Guía completa de integración
2. **[API_VENTAS_APP_MOVIL.md](API_VENTAS_APP_MOVIL.md)** - Crear ventas desde app móvil
3. **[API_APARTADOS_APP_MOVIL.md](API_APARTADOS_APP_MOVIL.md)** - Crear apartados desde app móvil

---

## 🌐 URL Base

```
https://inventario.sistemasdevida.com/api/v1
```

**Desarrollo local:**
```
http://localhost:8000/api/v1
```

---

## 📍 Endpoints Disponibles

| Método | Endpoint | Descripción | Documentación |
|--------|----------|-------------|---------------|
| GET | `/mis-subinventarios/{cod_congregante}` | Lista puntos de venta del usuario | [Ver docs](INTEGRACION_APP_MOVIL.md#1-listar-puntos-de-venta-del-usuario) |
| GET | `/subinventarios/{id}/libros` | Libros de un punto específico | [Ver docs](INTEGRACION_APP_MOVIL.md#2-cargar-libros-de-un-punto-de-venta) |
| POST | `/ventas` | Crear nueva venta | [Ver docs](API_VENTAS_APP_MOVIL.md) |
| POST | `/apartados` | Crear nuevo apartado | [Ver docs](API_APARTADOS_APP_MOVIL.md) |
| GET | `/clientes` | Lista de clientes | [Ver docs](INTEGRACION_APP_MOVIL.md) |
| GET | `/libros/buscar-codigo/{codigo}` | Buscar libro por código de barras | [Ver docs](INTEGRACION_APP_MOVIL.md) |

---

## 🔑 Autenticación

El sistema utiliza **token de congregante** (`cod_congregante`) para validar acceso:

```json
{
  "cod_congregante": "14279"
}
```

Este token se obtiene del sistema externo de autenticación:
```
POST https://www.sistemasdevida.com/pan/rest2/index.php/app/login
```

---

## 🆚 Ventas vs Apartados

### 💰 Ventas (Pago Completo)
- Pago completo o a crédito
- Entrega inmediata
- Stock se reduce de inmediato
- Cliente opcional (obligatorio para crédito)

**Ejemplo:**
```bash
POST /api/v1/ventas
{
  "subinventario_id": 1,
  "cod_congregante": "14279",
  "tipo_pago": "contado",
  "libros": [{"libro_id": 12, "cantidad": 2}]
}
```

### 📦 Apartados (Pago con Anticipo)
- Enganche + abonos posteriores
- Entrega al liquidar
- Stock se reserva (stock_apartado)
- Cliente obligatorio
- Fecha límite opcional

**Ejemplo:**
```bash
POST /api/v1/apartados
{
  "subinventario_id": 1,
  "cod_congregante": "14279",
  "cliente_id": 5,
  "enganche": 500.00,
  "fecha_limite": "2026-01-15",
  "libros": [{"libro_id": 12, "cantidad": 2, "precio_unitario": 350.00}]
}
```

---

## 🔄 Flujo de Trabajo Completo

```
┌─────────────────────────────────────────────────────┐
│ 1. AUTENTICACIÓN                                    │
│    POST https://sistemasdevida.com/.../app/login    │
│    → Obtiene cod_congregante                        │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ 2. LISTAR PUNTOS DE VENTA                           │
│    GET /api/v1/mis-subinventarios/{cod_congregante} │
│    → Usuario ve sus puntos asignados                │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ 3. CARGAR INVENTARIO                                │
│    GET /api/v1/subinventarios/{id}/libros           │
│    → Carga libros del punto seleccionado            │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
         ┌───────┴────────┐
         ▼                ▼
┌──────────────┐  ┌──────────────┐
│ 4a. VENTA    │  │ 4b. APARTADO │
│ POST /ventas │  │ POST /apartados │
│              │  │              │
│ • Pago total │  │ • Enganche   │
│ • Entrega ya │  │ • Abonos     │
└──────────────┘  └──────────────┘
```

---

## ✨ Características Principales

### ✅ Optimización de Rendimiento
- **Carga en 2 pasos**: Lista puntos → Carga libros bajo demanda
- Respuestas rápidas y livianas
- Menor consumo de datos/batería

### 🔒 Seguridad
- Validación de acceso por `cod_congregante`
- Transacciones de base de datos
- Rollback automático en errores
- Logging completo para auditoría

### 📊 Gestión de Stock
- **Stock disponible**: `stock - stock_apartado`
- Validación automática de cantidades
- Actualización en tiempo real
- Prevención de sobreventa

### 💼 Modalidades de Pago
- **Contado**: Pago completo al momento
- **Crédito**: Pago diferido con cliente asignado
- **Apartado**: Enganche + abonos posteriores

### 📦 Sistema de Apartados
- Reserva de inventario con anticipo
- Abonos parciales
- Fecha límite configurable
- Liquidación automática al completar pago

---

## 🧪 Pruebas Rápidas

### Listar mis puntos de venta
```bash
curl "https://inventario.sistemasdevida.com/api/v1/mis-subinventarios/14279"
```

### Ver libros de un punto
```bash
curl "https://inventario.sistemasdevida.com/api/v1/subinventarios/1/libros"
```

### Crear venta simple
```bash
curl -X POST "https://inventario.sistemasdevida.com/api/v1/ventas" \
  -H "Content-Type: application/json" \
  -d '{
    "subinventario_id": 1,
    "cod_congregante": "14279",
    "fecha_venta": "2026-01-08",
    "tipo_pago": "contado",
    "usuario": "Juan Pérez",
    "libros": [{"libro_id": 12, "cantidad": 2}]
  }'
```

### Crear apartado
```bash
curl -X POST "https://inventario.sistemasdevida.com/api/v1/apartados" \
  -H "Content-Type: application/json" \
  -d '{
    "subinventario_id": 1,
    "cod_congregante": "14279",
    "cliente_id": 1,
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
  }'
```

---

## 📱 Implementación en React Native

### Configuración Inicial

```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE = 'https://inventario.sistemasdevida.com/api/v1';

// Guardar token después del login
await AsyncStorage.setItem('codCongregante', response.codCongregante);
await AsyncStorage.setItem('username', response.nombre);
```

### Ejemplo de Integración Completa

```javascript
// 1. Listar puntos de venta
async function listarMisPuntosDeVenta() {
  const codCongregante = await AsyncStorage.getItem('codCongregante');
  const response = await fetch(`${API_BASE}/mis-subinventarios/${codCongregante}`);
  const data = await response.json();
  return data.data; // Array de subinventarios
}

// 2. Cargar libros de un punto
async function cargarLibrosPuntoVenta(subinventarioId) {
  const codCongregante = await AsyncStorage.getItem('codCongregante');
  const response = await fetch(
    `${API_BASE}/subinventarios/${subinventarioId}/libros?cod_congregante=${codCongregante}`
  );
  const data = await response.json();
  return data.data.libros;
}

// 3. Crear venta
async function crearVenta(librosCarrito) {
  const codCongregante = await AsyncStorage.getItem('codCongregante');
  const username = await AsyncStorage.getItem('username');
  
  const response = await fetch(`${API_BASE}/ventas`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      subinventario_id: 1,
      cod_congregante: codCongregante,
      fecha_venta: new Date().toISOString().split('T')[0],
      tipo_pago: 'contado',
      usuario: username,
      libros: librosCarrito,
    }),
  });
  
  const data = await response.json();
  return data;
}

// 4. Crear apartado
async function crearApartado(clienteId, enganche, librosCarrito) {
  const codCongregante = await AsyncStorage.getItem('codCongregante');
  const username = await AsyncStorage.getItem('username');
  
  const response = await fetch(`${API_BASE}/apartados`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      subinventario_id: 1,
      cod_congregante: codCongregante,
      cliente_id: clienteId,
      fecha_apartado: new Date().toISOString().split('T')[0],
      enganche: enganche,
      usuario: username,
      libros: librosCarrito,
    }),
  });
  
  const data = await response.json();
  return data;
}
```

---

## 🚀 Despliegue en Producción

### 1. Commit y Push
```bash
git add .
git commit -m "Add API REST para app móvil: ventas y apartados"
git push origin main
```

### 2. Actualizar Servidor
```bash
ssh usuario@servidor
cd /path/to/pan_control_interno
git pull origin main
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

### 3. Verificar
```bash
curl https://inventario.sistemasdevida.com/api/v1/mis-subinventarios/14279
```

---

## ❓ Solución de Problemas

### Error 403: "No tienes acceso a este punto de venta"
- Verificar que existe registro en tabla `subinventario_user`
- Confirmar `cod_congregante` correcto

### Error 422: "Stock insuficiente"
- Verificar stock disponible antes de enviar
- Mostrar solo cantidad disponible en UI

### Error 422: "El libro no está en este subinventario"
- Cargar libros del endpoint correcto
- Validar `libro_id` contra lista cargada

### Error 500: Error del servidor
- Revisar logs: `/storage/logs/laravel.log`
- Verificar conexión a base de datos
- Confirmar que el servidor esté operativo

---

## 📞 Soporte

Para más información:
- **Logs del servidor**: `/storage/logs/laravel.log`
- **Documentación técnica**: Ver archivos `.md` en el repositorio
- **Panel web**: https://inventario.sistemasdevida.com

---

## 📝 Changelog

### v1.0.0 (2026-01-08)

#### ✨ Nuevas Funcionalidades
- ✅ GET `/api/v1/mis-subinventarios/{cod_congregante}` - Listar puntos de venta
- ✅ GET `/api/v1/subinventarios/{id}/libros` - Cargar libros bajo demanda
- ✅ POST `/api/v1/ventas` - Crear ventas con todas las opciones
- ✅ POST `/api/v1/apartados` - Crear apartados con enganche y abonos

#### 🔒 Seguridad
- ✅ Validación de acceso por `cod_congregante`
- ✅ Transacciones de base de datos
- ✅ Logging de auditoría
- ✅ Rollback automático en errores

#### 📊 Características Ventas
- ✅ Tipos de pago: contado, crédito, mixto
- ✅ Descuentos globales e individuales
- ✅ Soporte para envíos
- ✅ Cliente opcional (obligatorio para crédito)
- ✅ Actualización automática de stock

#### 📦 Características Apartados
- ✅ Enganche configurable
- ✅ Fecha límite opcional
- ✅ Reserva de stock (stock_apartado)
- ✅ Cliente obligatorio
- ✅ Descuentos por libro
- ✅ Primer abono automático
- ✅ Generación de folio único

---

## 📄 Licencia

© 2026 Sistema de Inventario - Todos los derechos reservados
