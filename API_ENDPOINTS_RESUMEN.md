# Resumen de Endpoints API - Punto de Venta Móvil

## 📱 Flujo de Integración para App Móvil

### Paso 1: Listar Puntos de Venta del Usuario
```bash
GET /api/v1/mis-subinventarios/{cod_congregante}
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "descripcion": "Punto de Venta Central",
      "fecha_subinventario": "2026-01-05",
      "estado": "activo",
      "total_libros": 27,
      "total_unidades": 79
    }
  ]
}
```

**Ventajas:**
- ⚡ Respuesta rápida (no carga libros)
- 📊 Muestra totales de cada punto de venta
- 🔒 Filtra solo los puntos asignados al usuario

---

### Paso 2: Cargar Inventario del Punto Seleccionado
```bash
GET /api/v1/subinventarios/{id}/libros?cod_congregante={token}
```

**Ejemplo:**
```bash
GET /api/v1/subinventarios/1/libros?cod_congregante=14279
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "subinventario": {
      "id": 1,
      "descripcion": "Punto de Venta Central",
      "fecha_subinventario": "2026-01-05",
      "estado": "activo"
    },
    "total_libros": 27,
    "total_unidades": 79,
    "libros": [
      {
        "id": 12,
        "nombre": "Biblia Reina Valera 1960",
        "codigo_barras": "9788408234567",
        "precio": 25.50,
        "stock_general": 50,
        "cantidad_disponible": 10
      }
    ]
  }
}
```

**Ventajas:**
- 📚 Carga libros solo cuando se necesitan
- 🔒 Valida acceso del usuario (opcional con `cod_congregante`)
- 💰 Incluye precio y stock de cada libro
- ✅ Solo muestra libros con stock > 0

---

### Paso 3: Crear Venta
```bash
POST /api/v1/ventas
```

**Body:**
```json
{
  "subinventario_id": 1,
  "cod_congregante": "14279",
  "fecha_venta": "2026-01-07",
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

---

## 🔑 Ventajas de la Nueva Arquitectura

### Antes (Un Solo Endpoint)
```
GET /api/v1/mis-subinventarios/14279
└─ Carga TODO: subinventarios + todos sus libros
   ❌ Lento si hay muchos subinventarios
   ❌ Carga datos innecesarios
   ❌ Mayor uso de red/batería
```

### Ahora (Dos Endpoints)
```
1. GET /api/v1/mis-subinventarios/14279
   └─ Solo lista de subinventarios (ligero)
   ✅ Respuesta rápida
   ✅ Usuario ve sus opciones inmediatamente

2. GET /api/v1/subinventarios/1/libros
   └─ Carga libros solo del seleccionado
   ✅ Carga bajo demanda
   ✅ Mejor experiencia de usuario
   ✅ Ahorro de datos
```

---

## 🧪 Ejemplos de Uso con cURL

### 1. Listar mis puntos de venta
```bash
curl -X GET "https://inventario.sistemasdevida.com/api/v1/mis-subinventarios/14279" \
  -H "Accept: application/json"
```

### 2. Ver inventario de un punto
```bash
curl -X GET "https://inventario.sistemasdevida.com/api/v1/subinventarios/1/libros?cod_congregante=14279" \
  -H "Accept: application/json"
```

### 3. Crear venta
```bash
curl -X POST "https://inventario.sistemasdevida.com/api/v1/ventas" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "subinventario_id": 1,
    "cod_congregante": "14279",
    "fecha_venta": "2026-01-07",
    "tipo_pago": "contado",
    "usuario": "Juan Pérez",
    "libros": [
      {
        "libro_id": 12,
        "cantidad": 2
      }
    ]
  }'
```

---

## 🚀 Para Implementar en Producción

1. **Hacer commit:**
```bash
git add .
git commit -m "Refactorizar API: separar listado de subinventarios y carga de libros"
git push origin main
```

2. **En el servidor:**
```bash
cd /ruta/al/proyecto
git pull origin main
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

3. **Verificar:**
```bash
# Probar endpoint 1
curl https://inventario.sistemasdevida.com/api/v1/mis-subinventarios/14279

# Probar endpoint 2
curl https://inventario.sistemasdevida.com/api/v1/subinventarios/1/libros
```

---

## 📊 Endpoints Completos Disponibles

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/v1/mis-subinventarios/{cod_congregante}` | Lista puntos de venta del usuario |
| GET | `/api/v1/subinventarios/{id}/libros` | Libros de un punto específico |
| GET | `/api/v1/libros/buscar-codigo/{codigo}` | Buscar libro por código de barras |
| GET | `/api/v1/clientes` | Lista de clientes |
| POST | `/api/v1/ventas` | Crear nueva venta |

---

## ✅ Beneficios para la App Móvil

1. **Rendimiento Optimizado**
   - Carga inicial más rápida
   - Menor consumo de datos
   - Mejor experiencia de usuario

2. **Flexibilidad**
   - Usuario puede cambiar de punto de venta fácilmente
   - Recargar inventario cuando necesite

3. **Seguridad**
   - Validación de acceso en cada request
   - Usuario solo ve sus puntos asignados

4. **Escalabilidad**
   - Funciona bien con 1 o 100 subinventarios
   - No sobrecarga el servidor
