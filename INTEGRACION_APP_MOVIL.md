# Integración de App Móvil con API - Control Interno de Librería

## Resumen Ejecutivo

Este documento detalla cómo tu **aplicación móvil** (sistema separado) puede integrarse con la **API del sistema de Control Interno de Librería** para que un usuario ya autenticado pueda:

1. **Identificarse** en el sistema de librería
2. **Determinar qué inventario y subinventario** le corresponde administrar
3. **Realizar ventas** desde su punto de venta asignado

---

## 📋 Tabla de Contenidos

1. [Arquitectura de Autenticación](#arquitectura-de-autenticación)
2. [Flujo de Integración](#flujo-de-integración)
3. [Endpoints Disponibles](#endpoints-disponibles)
4. [Implementación Paso a Paso](#implementación-paso-a-paso)
5. [Validación de Permisos](#validación-de-permisos)
6. [Ejemplos de Código](#ejemplos-de-código)
7. [Casos de Uso](#casos-de-uso)
8. [Limitaciones Actuales y Recomendaciones](#limitaciones-actuales-y-recomendaciones)

---

## 🔐 Arquitectura de Autenticación

### Sistema Actual

El proyecto utiliza un **sistema de autenticación híbrido**:

```
┌─────────────────────────────────────────────────────────────────┐
│                     FLUJO DE AUTENTICACIÓN                       │
└─────────────────────────────────────────────────────────────────┘

1. Usuario ingresa credenciales (user, password)
                    ↓
2. Sistema llama API Externa
   POST https://www.sistemasdevida.com/pan/rest2/index.php/app/login
                    ↓
3. API Externa responde con:
   - token (codCongregante)
   - roles
   - codCasaVida
   - codHogar
                    ↓
4. Sistema verifica rol "ADMIN LIBRERIA"
                    ↓
5. Se guarda en sesión:
   - codCongregante (token del usuario)
   - username
   - roles
```

**Código: `app/Http/Controllers/AuthController.php`**

### Base de Datos: Relación Usuario-SubInventario

Existe una tabla pivot que relaciona usuarios externos con subinventarios:

**Tabla:** `subinventario_user`
```sql
CREATE TABLE subinventario_user (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    subinventario_id BIGINT,  -- FK a subinventarios
    cod_congregante VARCHAR,   -- Token/código del usuario de API externa
    nombre_congregante VARCHAR, -- Nombre (cache)
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE (subinventario_id, cod_congregante)
);
```

**Migración:** `database/migrations/2026_01_04_071741_create_subinventario_user_table.php`

---

## 🔄 Flujo de Integración

### Escenario: Usuario en App Móvil

```
┌──────────────────────────────────────────────────────────────┐
│  APP MÓVIL (Sistema Externo)                                  │
└──────────────────────────────────────────────────────────────┘
         │
         │ Usuario ya está autenticado
         │ Tiene: codCongregante (token)
         │
         ↓
┌──────────────────────────────────────────────────────────────┐
│  PASO 1: Obtener Mis Subinventarios                          │
│  GET /api/v1/mis-subinventarios/{codCongregante}             │
└──────────────────────────────────────────────────────────────┘
         │
         │ Respuesta: Lista de subinventarios asignados
         │ (solo info básica: id, nombre, totales)
         │
         ↓
┌──────────────────────────────────────────────────────────────┐
│  PASO 2: Usuario Selecciona un Punto de Venta               │
│  App muestra lista de subinventarios                         │
│  Usuario selecciona uno (ej: subinventario_id = 5)          │
└──────────────────────────────────────────────────────────────┘
         │
         │
         ↓
┌──────────────────────────────────────────────────────────────┐
│  PASO 3: Cargar Inventario del Punto de Venta               │
│  GET /api/v1/subinventarios/5/libros                         │
│       ?cod_congregante={codCongregante} (opcional)           │
└──────────────────────────────────────────────────────────────┘
         │
         │ Respuesta: Todos los libros con stock disponible
         │ - ID, nombre, precio, cantidad_disponible
         │
         ↓
┌──────────────────────────────────────────────────────────────┐
│  PASO 4: Realizar Ventas                                     │
│  Usuario selecciona libros y cantidades                      │
│  POST /api/v1/ventas                                         │
│  Body: {                                                     │
│    subinventario_id: 5,                                      │
│    usuario: "nombre_usuario",                                │
│    libros: [...]                                             │
│  }                                                           │
└──────────────────────────────────────────────────────────────┘
```

---

## 🌐 Endpoints Disponibles

### 1. **GET** `/api/v1/mis-subinventarios/{cod_congregante}`

**Descripción:** Obtiene la lista de subinventarios asignados al usuario (sin libros, solo información básica)

**Path Parameters:**
- `cod_congregante` (obligatorio): Token/código del usuario

**Respuesta de Ejemplo:**
```json
{
  "success": true,
  "message": "Subinventarios encontrados",
  "data": [
    {
      "id": 1,
      "descripcion": "Punto de Venta - Juan Pérez",
      "fecha_subinventario": "2026-01-05",
      "estado": "activo",
      "observaciones": "Asignado para enero",
      "total_libros": 27,
      "total_unidades": 79
    }
  ]
}
```

**Respuesta (sin subinventarios):**
```json
{
  "success": false,
  "message": "No tienes subinventarios asignados",
  "data": []
}
```

---

### 2. **GET** `/api/v1/subinventarios/{id}/libros`

**Descripción:** Obtiene todos los libros disponibles de un subinventario específico con stock y precios

**Path Parameters:**
- `id` (obligatorio): ID del subinventario

**Query Parameters (opcional):**
- `cod_congregante` (string): Para validar acceso del usuario al subinventario

**Respuesta de Ejemplo:**
```json
{
  "success": true,
  "message": "Libros encontrados",
  "data": {
    "subinventario": {
      "id": 1,
      "descripcion": "Punto de Venta - Juan Pérez",
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
      },
      {
        "id": 23,
        "nombre": "Devocional Jesús Te Llama",
        "codigo_barras": "9780718034047",
        "precio": 15.00,
        "stock_general": 30,
        "cantidad_disponible": 5
      }
    ]
  }
}
```

**Respuesta Error (sin acceso):**
```json
{
  "success": false,
  "message": "No tienes acceso a este subinventario"
}
```

---

### 3. **GET** `/api/v1/subinventarios`

**Descripción:** Obtiene lista de todos los subinventarios (uso interno/admin)

**Query Parameters:**
| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| `estado` | string | Filtrar por estado | `activo`, `completado`, `cancelado` |
| `fecha` | date | Filtrar por fecha | `2026-01-07` |
| `search` | string | Buscar en descripción | `Punto Venta 1` |
| `ordenar` | string | Orden de resultados | `reciente`, `antiguo`, `fecha_asc`, `fecha_desc` |
| `per_page` | integer | Resultados por página | `15` (default) |

**Código Fuente:** `app/Http/Controllers/SubInventarioController.php:511` (método `apiIndex`)

---

### 4. **GET** `/api/v1/libros/buscar-codigo/{codigo}`

**Descripción:** Buscar libro por código de barras o QR

**Path Parameters:**
- `codigo` (obligatorio): Código de barras del libro

**Respuesta de Ejemplo:**
```json
{
  "success": true,
  "libro": {
    "id": 12,
    "nombre": "Biblia Reina Valera 1960",
    "codigo_barras": "9788408234567",
    "precio": 25.50,
    "stock": 50,
    "stock_subinventario": 15
  }
}
```

---

### 5. **GET** `/api/v1/clientes`

**Descripción:** Obtiene lista de clientes (para asignar a ventas)

**Query Parameters:**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `search` | string | Buscar por nombre/cédula |
| `per_page` | integer | Resultados por página |

**Respuesta de Ejemplo:**
```json
{
  "data": [
    {
      "id": 3,
      "cedula": "001-1234567-8",
      "nombre": "María González",
      "telefono": "809-555-1234",
      "direccion": "Calle Principal #45",
      "email": "maria@example.com"
    }
  ]
}
```

---

### 6. **POST** `/api/v1/ventas`

**Descripción:** Crear nueva venta desde la app móvil

**Body (JSON):**
```json
{
  "subinventario_id": 5,           // OBLIGATORIO - ID del subinventario
  "cliente_id": 3,                  // OPCIONAL - ID del cliente
  "fecha_venta": "2026-01-07",     // OBLIGATORIO
  "tipo_pago": "contado",          // OBLIGATORIO: contado|credito|mixto
  "descuento_global": 0,           // OPCIONAL: 0-100 (porcentaje)
  "observaciones": "Venta app móvil", // OPCIONAL
  "usuario": "Juan Pérez",         // OBLIGATORIO - Nombre del usuario
  "libros": [                       // OBLIGATORIO - Array mínimo 1 libro
    {
      "libro_id": 12,
      "cantidad": 2,
      "descuento": 0                // OPCIONAL: 0-100 (porcentaje)
    },
    {
      "libro_id": 23,
      "cantidad": 1,
      "descuento": 10
    }
  ]
}
```

**Validaciones Automáticas:**
- ✅ Verifica que los libros existan en el subinventario
- ✅ Verifica que haya stock suficiente
- ✅ Actualiza inventario automáticamente
- ✅ Crea movimientos de salida
- ✅ Calcula totales con descuentos

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Venta creada exitosamente",
  "data": {
    "venta_id": 145,
    "total": 48.95
  }
}
```

**Respuesta Error (422):**
```json
{
  "success": false,
  "message": "El libro ID 12 no está en este subinventario"
}
```

**Código Fuente:** `app/Http/Controllers/VentaController.php:957` (método `apiStore`)

---

## 🚀 Implementación Paso a Paso

### **Opción A: Con Endpoint Adicional (RECOMENDADO)**

Actualmente **no existe** un endpoint específico para obtener subinventarios por `cod_congregante`. Te recomiendo crearlo:

#### **PASO 1: Crear Nuevo Endpoint**

**Archivo:** `routes/api.php`

```php
// Agregar esta ruta
Route::prefix('v1')->group(function () {
    // ... rutas existentes ...
    
    // Nueva ruta para obtener subinventarios por usuario
    Route::get('/mis-subinventarios/{cod_congregante}', 
        [SubInventarioController::class, 'apiMisSubinventarios']);
});
```

#### **PASO 2: Crear Método en Controlador**

**Archivo:** `app/Http/Controllers/SubInventarioController.php`

```php
/**
 * API - Obtener subinventarios asignados a un usuario específico
 */
public function apiMisSubinventarios(Request $request, $codCongregante)
{
    // Buscar subinventarios donde el usuario tiene acceso
    $subinventariosIds = DB::table('subinventario_user')
        ->where('cod_congregante', $codCongregante)
        ->pluck('subinventario_id');
    
    if ($subinventariosIds->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes subinventarios asignados',
            'data' => []
        ], 404);
    }
    
    // Obtener los subinventarios con sus libros
    $subinventarios = SubInventario::with(['libros' => function($query) {
            $query->select('libros.id', 'libros.nombre', 'libros.codigo_barras', 
                          'libros.precio', 'libros.descripcion')
                  ->where('subinventario_libro.cantidad', '>', 0); // Solo libros con stock
        }])
        ->whereIn('id', $subinventariosIds)
        ->where('estado', 'activo') // Solo activos
        ->get()
        ->map(function($subinventario) {
            return [
                'id' => $subinventario->id,
                'descripcion' => $subinventario->descripcion,
                'fecha_subinventario' => $subinventario->fecha_subinventario,
                'estado' => $subinventario->estado,
                'total_libros' => $subinventario->libros->count(),
                'total_unidades' => $subinventario->libros->sum('pivot.cantidad'),
                'libros' => $subinventario->libros->map(function($libro) {
                    return [
                        'id' => $libro->id,
                        'nombre' => $libro->nombre,
                        'codigo_barras' => $libro->codigo_barras,
                        'precio' => $libro->precio,
                        'descripcion' => $libro->descripcion,
                        'cantidad_disponible' => $libro->pivot->cantidad
                    ];
                })
            ];
        });
    
    return response()->json([
        'success' => true,
        'message' => 'Subinventarios encontrados',
        'data' => $subinventarios
    ], 200);
}
```

#### **PASO 3: Uso desde tu App Móvil**

```javascript
// Ejemplo en JavaScript/React Native
async function obtenerMiInventario() {
    const codCongregante = await AsyncStorage.getItem('token'); // Tu token
    
    try {
        const response = await fetch(
            `https://tu-dominio.com/api/v1/mis-subinventarios/${codCongregante}`
        );
        
        const data = await response.json();
        
        if (data.success) {
            // data.data contiene array de subinventarios asignados
            console.log('Mis inventarios:', data.data);
            
            // Guardar en estado local
            setMisInventarios(data.data);
        } else {
            alert('No tienes puntos de venta asignados');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}
```

---

### **Opción B: Sin Endpoint Adicional (Alternativa)**

Si no puedes modificar el backend, puedes hacer la validación en tu app:

```javascript
async function validarAccesoSubinventario(subinventarioId, codCongregante) {
    try {
        // Consultar directamente a la base de datos 
        // (requiere exponerlo como endpoint o usar GraphQL)
        const response = await fetch(
            `https://tu-dominio.com/api/v1/validar-acceso`, 
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    subinventario_id: subinventarioId,
                    cod_congregante: codCongregante
                })
            }
        );
        
        return response.json();
    } catch (error) {
        return { tieneAcceso: false };
    }
}
```

---

## 🔒 Validación de Permisos

### Sistema Actual en Ventas Web

El código actual **valida el acceso** cuando se crean ventas desde subinventario:

**Archivo:** `app/Http/Controllers/VentaController.php:250-260`

```php
// Validar acceso al subinventario
if ($tipoInventario === 'subinventario') {
    $tieneAcceso = DB::table('subinventario_user')
        ->where('subinventario_id', $subinventarioId)
        ->where('cod_congregante', session('codCongregante'))
        ->exists();
    
    if (!$tieneAcceso) {
        return back()->withErrors([
            'error' => 'No tienes acceso a este punto de venta (subinventario)'
        ])->withInput();
    }
}
```

### ⚠️ **PROBLEMA: API No Valida Permisos**

El endpoint `/api/v1/ventas` **NO valida** el `cod_congregante` actualmente. 

**Recomendación: Agregar validación**

```php
// En VentaController.php, método apiStore (línea 957)
public function apiStore(Request $request)
{
    $validated = $request->validate([
        'subinventario_id' => 'required|exists:subinventarios,id',
        'cod_congregante' => 'required|string', // AGREGAR ESTE CAMPO
        // ... resto de validaciones
    ]);

    DB::beginTransaction();
    try {
        // AGREGAR ESTA VALIDACIÓN
        $tieneAcceso = DB::table('subinventario_user')
            ->where('subinventario_id', $validated['subinventario_id'])
            ->where('cod_congregante', $validated['cod_congregante'])
            ->exists();
        
        if (!$tieneAcceso) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este subinventario'
            ], 403);
        }
        
        // ... resto del código
```

---

## 💻 Ejemplos de Código

### Ejemplo Completo: App Móvil React Native

```javascript
import React, { useState, useEffect } from 'react';
import { View, Text, FlatList, Button, ActivityIndicator } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE = 'https://tu-dominio.com/api/v1';

function PuntoVentaScreen() {
    const [codCongregante, setCodCongregante] = useState(null);
    const [subinventarios, setSubinventarios] = useState([]);
    const [selectedSubinv, setSelectedSubinv] = useState(null);
    const [libros, setLibros] = useState([]);
    const [loading, setLoading] = useState(true);
    const [loadingLibros, setLoadingLibros] = useState(false);
    
    useEffect(() => {
        inicializar();
    }, []);
    
    // 1. Cargar token del usuario autenticado
    async function inicializar() {
        const token = await AsyncStorage.getItem('codCongregante');
        setCodCongregante(token);
        
        if (token) {
            await cargarMisSubinventarios(token);
        }
    }
    
    // 2. Obtener lista de subinventarios asignados (sin libros)
    async function cargarMisSubinventarios(token) {
        try {
            setLoading(true);
            
            const response = await fetch(
                `${API_BASE}/mis-subinventarios/${token}`
            );
            
            const data = await response.json();
            
            if (data.success) {
                setSubinventarios(data.data);
                
                // Si solo hay uno, seleccionarlo automáticamente
                if (data.data.length === 1) {
                    await seleccionarSubinventario(data.data[0]);
                }
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error cargando inventarios:', error);
            alert('Error al cargar tus puntos de venta');
        } finally {
            setLoading(false);
        }
    }
    
    // 3. Cargar libros del subinventario seleccionado
    async function seleccionarSubinventario(subinv) {
        try {
            setLoadingLibros(true);
            setSelectedSubinv(subinv);
            
            // Cargar libros con validación de acceso
            const response = await fetch(
                `${API_BASE}/subinventarios/${subinv.id}/libros?cod_congregante=${codCongregante}`
            );
            
            const data = await response.json();
            
            if (data.success) {
                setLibros(data.data.libros);
            } else {
                alert(data.message);
                setSelectedSubinv(null);
            }
        } catch (error) {
            console.error('Error cargando libros:', error);
            alert('Error al cargar el inventario');
            setSelectedSubinv(null);
        } finally {
            setLoadingLibros(false);
        }
    }
    
    // 4. Crear venta
    async function crearVenta(librosSeleccionados) {
        if (!selectedSubinv) {
            alert('Debes seleccionar un punto de venta');
            return;
        }
        
        const username = await AsyncStorage.getItem('username');
        
        const body = {
            subinventario_id: selectedSubinv.id,
            cod_congregante: codCongregante, // Para validación
            fecha_venta: new Date().toISOString().split('T')[0],
            tipo_pago: 'contado',
            usuario: username,
            libros: librosSeleccionados.map(item => ({
                libro_id: item.libro_id,
                cantidad: item.cantidad,
                descuento: item.descuento || 0
            }))
        };
        
        try {
            const response = await fetch(`${API_BASE}/ventas`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(body)
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert(`Venta creada! Total: $${data.data.total}`);
                // Recargar inventario del punto de venta
                await seleccionarSubinventario(selectedSubinv);
            } else {
                alert(`Error: ${data.message}`);
            }
        } catch (error) {
            console.error('Error creando venta:', error);
            alert('Error al procesar la venta');
        }
    }
    
    // Renderizado
    if (loading) {
        return (
            <View style={{flex: 1, justifyContent: 'center', alignItems: 'center'}}>
                <ActivityIndicator size="large" />
                <Text>Cargando tus puntos de venta...</Text>
            </View>
        );
    }
    
    if (subinventarios.length === 0) {
        return (
            <View style={{flex: 1, justifyContent: 'center', alignItems: 'center', padding: 20}}>
                <Text style={{fontSize: 18, textAlign: 'center'}}>
                    No tienes puntos de venta asignados
                </Text>
                <Text style={{marginTop: 10, color: '#666'}}>
                    Contacta al administrador
                </Text>
            </View>
        );
    }
    
    // Si no ha seleccionado un punto de venta, mostrar lista
    if (!selectedSubinv) {
        return (
            <View style={{flex: 1, padding: 20}}>
                <Text style={{fontSize: 24, fontWeight: 'bold', marginBottom: 20}}>
                    Mis Puntos de Venta
                </Text>
                
                <FlatList
                    data={subinventarios}
                    keyExtractor={item => item.id.toString()}
                    renderItem={({item}) => (
                        <View style={{
                            backgroundColor: '#f5f5f5',
                            padding: 15,
                            marginBottom: 10,
                            borderRadius: 8
                        }}>
                            <Text style={{fontSize: 18, fontWeight: 'bold'}}>
                                {item.descripcion || `Punto de Venta #${item.id}`}
                            </Text>
                            <Text style={{color: '#666', marginTop: 5}}>
                                Libros: {item.total_libros} | Unidades: {item.total_unidades}
                            </Text>
                            <Text style={{color: '#666', fontSize: 12}}>
                                Fecha: {new Date(item.fecha_subinventario).toLocaleDateString()}
                            </Text>
                            <Button 
                                title="Seleccionar"
                                onPress={() => seleccionarSubinventario(item)}
                            />
                        </View>
                    )}
                />
            </View>
        );
    }
    
    // Vista del punto de venta seleccionado
    return (
        <View style={{flex: 1}}>
            <View style={{backgroundColor: '#007bff', padding: 15}}>
                <Text style={{color: 'white', fontSize: 20, fontWeight: 'bold'}}>
                    {selectedSubinv.descripcion || `Punto de Venta #${selectedSubinv.id}`}
                </Text>
                <Text style={{color: 'white'}}>
                    {selectedSubinv.total_libros} libros - {selectedSubinv.total_unidades} unidades
                </Text>
                <Button 
                    title="Cambiar punto de venta"
                    color="#fff"
                    onPress={() => {
                        setSelectedSubinv(null);
                        setLibros([]);
                    }}
                />
            </View>
            
            {loadingLibros ? (
                <View style={{flex: 1, justifyContent: 'center', alignItems: 'center'}}>
                    <ActivityIndicator size="large" />
                    <Text>Cargando inventario...</Text>
                </View>
            ) : (
                <FlatList
                    data={libros}
                    keyExtractor={item => item.id.toString()}
                    renderItem={({item}) => (
                        <View style={{
                            padding: 15,
                            borderBottomWidth: 1,
                            borderBottomColor: '#eee'
                        }}>
                            <Text style={{fontSize: 16, fontWeight: 'bold'}}>
                                {item.nombre}
                            </Text>
                            <Text style={{color: '#666'}}>
                                Disponible: {item.cantidad_disponible} unidades
                            </Text>
                            <Text style={{color: '#007bff', fontSize: 18, fontWeight: 'bold'}}>
                                ${item.precio}
                            </Text>
                            {item.codigo_barras && (
                                <Text style={{fontSize: 12, color: '#999'}}>
                                    Código: {item.codigo_barras}
                                </Text>
                            )}
                        </View>
                    )}
                />
            )}
        </View>
    );
}

export default PuntoVentaScreen;
```

---

## 📊 Casos de Uso

### Caso 1: Usuario con UN Solo Subinventario

```
Usuario: Juan Pérez
codCongregante: "ABC123XYZ"

Flujo:
1. App consulta: GET /api/v1/mis-subinventarios/ABC123XYZ
2. Respuesta: 
   [
     { id: 5, descripcion: "Punto Venta Juan", libros: [...] }
   ]
3. App selecciona automáticamente el subinventario #5
4. Usuario puede crear ventas directamente
```

### Caso 2: Usuario con MÚLTIPLES Subinventarios

```
Usuario: María Admin
codCongregante: "ADMIN456"

Flujo:
1. App consulta: GET /api/v1/mis-subinventarios/ADMIN456
2. Respuesta:
   [
     { id: 3, descripcion: "Sucursal Centro" },
     { id: 7, descripcion: "Sucursal Norte" },
     { id: 9, descripcion: "Feria del Libro" }
   ]
3. App muestra selector para elegir el punto de venta
4. Usuario selecciona uno antes de crear ventas
```

### Caso 3: Usuario SIN Subinventarios

```
Usuario: Pedro Nuevo
codCongregante: "NEW789"

Flujo:
1. App consulta: GET /api/v1/mis-subinventarios/NEW789
2. Respuesta:
   {
     "success": false,
     "message": "No tienes subinventarios asignados"
   }
3. App muestra mensaje: "Contacta al administrador para asignarte un punto de venta"
```

---

## ⚠️ Limitaciones Actuales y Recomendaciones

### **Limitaciones**

1. **❌ No hay autenticación API (sin tokens JWT/Sanctum)**
   - Las rutas API son completamente públicas
   - No hay middleware de autenticación

2. **❌ No existe endpoint para consultar subinventarios por usuario**
   - Debes crear el endpoint `apiMisSubinventarios`

3. **❌ API de ventas no valida `cod_congregante`**
   - Cualquiera puede crear ventas en cualquier subinventario
   - Requiere agregar validación de permisos

4. **❌ No hay rate limiting**
   - Las APIs pueden ser abusadas con múltiples requests

### **Recomendaciones Críticas**

#### 1. **Implementar Laravel Sanctum (Autenticación API)**

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

**Configurar en `config/sanctum.php`:**
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 
    'localhost,127.0.0.1,tu-app-movil.com')),
```

**Proteger rutas API:**
```php
// routes/api.php
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/mis-subinventarios/{cod_congregante}', ...);
    Route::post('/ventas', ...);
    // ...
});
```

#### 2. **Crear Endpoint de Login para App Móvil**

```php
// AuthController.php
public function apiLogin(Request $request)
{
    $validated = $request->validate([
        'user' => 'required|string',
        'contra' => 'required|string',
    ]);
    
    // Llamar API externa
    $response = Http::post('https://www.sistemasdevida.com/pan/rest2/index.php/app/login', $validated);
    
    if (!$response->successful()) {
        return response()->json(['error' => 'Credenciales inválidas'], 401);
    }
    
    $data = $response->json();
    
    // Verificar rol
    $tieneRolAdminLibreria = collect($data['roles'])->contains(function ($rol) {
        return strtoupper(trim($rol['ROL'])) === 'ADMIN LIBRERIA';
    });
    
    if (!$tieneRolAdminLibreria) {
        return response()->json(['error' => 'Sin permisos'], 403);
    }
    
    // Crear o buscar usuario local (opcional)
    // $user = User::firstOrCreate(['email' => $data['token']], [...]);
    
    // Generar token Sanctum
    // $token = $user->createToken('mobile-app')->plainTextToken;
    
    return response()->json([
        'success' => true,
        'cod_congregante' => $data['token'],
        'username' => $validated['user'],
        'roles' => $data['roles'],
        // 'token' => $token, // Token Sanctum para futuras requests
    ]);
}
```

#### 3. **Agregar Validación de Permisos en Ventas API**

Ya mostrado anteriormente en la sección de validación.

#### 4. **Implementar Rate Limiting**

```php
// app/Http/Kernel.php (Laravel 10) o bootstrap/app.php (Laravel 11)
'api' => [
    'throttle:60,1', // 60 requests por minuto
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

#### 5. **Logging y Auditoría**

```php
// En VentaController::apiStore
Log::info('Venta creada desde API móvil', [
    'venta_id' => $venta->id,
    'usuario' => $validated['usuario'],
    'cod_congregante' => $validated['cod_congregante'],
    'subinventario_id' => $validated['subinventario_id'],
    'total' => $venta->total,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

---

## 🔧 Checklist de Implementación

- [ ] Crear endpoint `/api/v1/mis-subinventarios/{cod_congregante}`
- [ ] Agregar validación de `cod_congregante` en `/api/v1/ventas`
- [ ] Implementar Laravel Sanctum
- [ ] Crear endpoint `/api/v1/login` para app móvil
- [ ] Proteger rutas API con middleware `auth:sanctum`
- [ ] Agregar rate limiting
- [ ] Implementar logging de operaciones API
- [ ] Documentar API con Postman o Swagger
- [ ] Crear pruebas unitarias para endpoints
- [ ] Configurar CORS adecuadamente

---

## 📞 Soporte

Para implementar estas recomendaciones:

1. **Prioridad Alta:** Endpoint `apiMisSubinventarios` + Validación de permisos
2. **Prioridad Media:** Laravel Sanctum
3. **Prioridad Baja:** Rate limiting y logging avanzado

---

## 📝 Conclusión

Tu app móvil puede integrarse con el sistema siguiendo estos pasos:

1. **Identificar usuario:** Usar el `codCongregante` (token) que ya tienes de tu sistema de autenticación externo
2. **Consultar inventarios:** Llamar al nuevo endpoint `/api/v1/mis-subinventarios/{cod_congregante}` 
3. **Crear ventas:** Usar `/api/v1/ventas` pasando el `subinventario_id` correspondiente

**Sin embargo**, es **CRÍTICO** implementar:
- Validación de permisos en el endpoint de ventas
- Autenticación API con Sanctum para seguridad
- Logging de operaciones para auditoría

Esto garantizará que cada usuario solo pueda administrar los inventarios que le corresponden y que el sistema sea seguro.
