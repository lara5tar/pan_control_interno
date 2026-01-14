# API - Disponibilidad de Libros

## 📋 Resumen Rápido

Esta API te permite verificar si un libro tiene existencia en el **inventario general** y/o en **subinventarios**, ideal para tu app móvil.

---

## 🔗 Endpoint

```
GET /api/v1/libros/{id}/disponibilidad
```

**Parámetros:**
- `{id}` - ID del libro que quieres consultar

---

## 📤 Respuesta

### Ejemplo 1: Libro SOLO en Inventario General
```json
{
    "success": true,
    "data": {
        "libro": {
            "id": 1,
            "nombre": "21 dias 1",
            "codigo_barras": null,
            "precio": 8
        },
        "inventario_general": {
            "disponible": true,
            "cantidad": 1609
        },
        "subinventarios": [],
        "total_disponible": 1609,
        "tiene_stock": true
    }
}
```

### Ejemplo 2: Libro SOLO en Subinventario
```json
{
    "success": true,
    "data": {
        "libro": {
            "id": 178,
            "nombre": "BIBLIA THOMPSON",
            "codigo_barras": null,
            "precio": 900
        },
        "inventario_general": {
            "disponible": false,
            "cantidad": 0
        },
        "subinventarios": [
            {
                "subinventario_id": 1,
                "descripcion": "Sin descripción",
                "fecha_subinventario": "2025-12-30",
                "cantidad_disponible": 1
            }
        ],
        "total_disponible": 1,
        "tiene_stock": true
    }
}
```

### Ejemplo 3: Libro en AMBOS Lugares
```json
{
    "success": true,
    "data": {
        "libro": {
            "id": 50,
            "nombre": "Ejemplo Libro",
            "codigo_barras": "123456789",
            "precio": 150
        },
        "inventario_general": {
            "disponible": true,
            "cantidad": 25
        },
        "subinventarios": [
            {
                "subinventario_id": 1,
                "descripcion": "Feria del Libro 2025",
                "fecha_subinventario": "2025-12-30",
                "cantidad_disponible": 10
            },
            {
                "subinventario_id": 3,
                "descripcion": "Campaña Navidad",
                "fecha_subinventario": "2025-12-15",
                "cantidad_disponible": 5
            }
        ],
        "total_disponible": 40,
        "tiene_stock": true
    }
}
```

### Ejemplo 4: Libro NO Encontrado
```json
{
    "success": false,
    "message": "Libro no encontrado"
}
```

---

## 📊 Estructura de la Respuesta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `success` | boolean | `true` si la petición fue exitosa |
| `data.libro` | object | Información básica del libro |
| `data.libro.id` | integer | ID del libro |
| `data.libro.nombre` | string | Nombre del libro |
| `data.libro.codigo_barras` | string\|null | Código de barras (si existe) |
| `data.libro.precio` | number | Precio del libro |
| `data.inventario_general` | object | Info del inventario general |
| `data.inventario_general.disponible` | boolean | `true` si hay stock en inventario general |
| `data.inventario_general.cantidad` | integer | Cantidad en inventario general |
| `data.subinventarios` | array | Lista de subinventarios con stock |
| `data.subinventarios[].subinventario_id` | integer | ID del subinventario |
| `data.subinventarios[].descripcion` | string | Descripción del subinventario |
| `data.subinventarios[].fecha_subinventario` | string | Fecha del subinventario (YYYY-MM-DD) |
| `data.subinventarios[].cantidad_disponible` | integer | Cantidad en ese subinventario |
| `data.total_disponible` | integer | Suma total de todas las existencias |
| `data.tiene_stock` | boolean | `true` si hay stock en cualquier lugar |

---

## 💡 Casos de Uso

### 1️⃣ Verificar si hay stock antes de vender
```javascript
// En tu app móvil
const libroId = 178;
const response = await fetch(`http://tu-servidor.com/api/v1/libros/${libroId}/disponibilidad`);
const data = await response.json();

if (data.data.tiene_stock) {
    console.log(`✅ Hay ${data.data.total_disponible} unidades disponibles`);
    
    if (data.data.inventario_general.disponible) {
        console.log(`- Inventario general: ${data.data.inventario_general.cantidad}`);
    }
    
    if (data.data.subinventarios.length > 0) {
        console.log('- En subinventarios:');
        data.data.subinventarios.forEach(sub => {
            console.log(`  * ${sub.descripcion}: ${sub.cantidad_disponible}`);
        });
    }
} else {
    console.log('❌ Sin stock disponible');
}
```

### 2️⃣ Mostrar disponibilidad en la UI
```javascript
function mostrarDisponibilidad(data) {
    if (!data.data.tiene_stock) {
        return '❌ Agotado';
    }
    
    const partes = [];
    
    if (data.data.inventario_general.disponible) {
        partes.push(`General: ${data.data.inventario_general.cantidad}`);
    }
    
    if (data.data.subinventarios.length > 0) {
        const totalSub = data.data.subinventarios.reduce((sum, s) => sum + s.cantidad_disponible, 0);
        partes.push(`Subinventarios: ${totalSub}`);
    }
    
    return `✅ Disponible (${partes.join(' | ')})`;
}
```

### 3️⃣ Decidir desde dónde vender
```javascript
async function decidirOrigenVenta(libroId, cantidadRequerida) {
    const response = await fetch(`/api/v1/libros/${libroId}/disponibilidad`);
    const data = await response.json();
    
    // Prioridad 1: Inventario general
    if (data.data.inventario_general.cantidad >= cantidadRequerida) {
        return { tipo: 'general', mensaje: 'Vender desde inventario general' };
    }
    
    // Prioridad 2: Buscar en subinventarios
    for (const sub of data.data.subinventarios) {
        if (sub.cantidad_disponible >= cantidadRequerida) {
            return { 
                tipo: 'subinventario', 
                subinventario_id: sub.subinventario_id,
                mensaje: `Vender desde ${sub.descripcion}`
            };
        }
    }
    
    // Sin suficiente stock
    return { tipo: null, mensaje: 'Stock insuficiente' };
}
```

---

## 🔐 Notas Importantes

1. **Sin Autenticación**: Este endpoint NO requiere autenticación (puedes agregarlo si lo necesitas)
2. **Subinventarios Activos**: Solo muestra subinventarios con estado `activo` y cantidad > 0
3. **Stock Total**: El campo `total_disponible` suma inventario general + todos los subinventarios
4. **Performance**: La consulta usa eager loading para optimizar el rendimiento

---

## 🧪 Pruebas con cURL

```bash
# Libro en inventario general
curl http://127.0.0.1:8000/api/v1/libros/1/disponibilidad

# Libro en subinventario
curl http://127.0.0.1:8000/api/v1/libros/178/disponibilidad

# Libro inexistente (404)
curl http://127.0.0.1:8000/api/v1/libros/99999/disponibilidad
```

---

## ✅ Resumen en 3 Pasos

1. **Haz una petición GET** a `/api/v1/libros/{id}/disponibilidad`
2. **Revisa** `data.tiene_stock` para saber si hay existencias
3. **Usa** `inventario_general` y `subinventarios` para ver dónde está el stock

🎯 **Listo para usar en tu app móvil!**
