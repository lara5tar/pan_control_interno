# 📊 ANÁLISIS: Cómo Funcionan los Sub-Inventarios en Ventas

## 📋 Tabla de Contenidos
1. [Visión General](#visión-general)
2. [Flujo de Datos](#flujo-de-datos)
3. [Proceso de Venta](#proceso-de-venta)
4. [Cálculo de Stock](#cálculo-de-stock)
5. [Casos de Uso](#casos-de-uso)
6. [Diagramas](#diagramas)

---

## 🎯 Visión General

El sistema de sub-inventarios permite crear **puntos de venta** con una cantidad limitada de libros. Cuando se realiza una venta, el stock se descuenta del sub-inventario específico.

### Tipos de Inventario en Ventas:
```
┌─────────────────────────────────────────┐
│         TIPO DE INVENTARIO              │
├─────────────────────────────────────────┤
│ 1. GENERAL                              │
│    • Stock disponible en oficina central │
│    • Para todos                          │
│    • Sin restricción de punto de venta   │
│                                         │
│ 2. SUBINVENTARIO                        │
│    • Stock en un punto de venta          │
│    • Solo libros asignados al punto     │
│    • Cantidad limitada por asignación   │
└─────────────────────────────────────────┘
```

---

## 🔄 Flujo de Datos

### 1️⃣ CREAR SUB-INVENTARIO
```
┌──────────────────────────┐
│  Admin Librería          │
│  Crea Sub-Inventario     │
│  (Ej: Día de evento)     │
└────────────┬─────────────┘
             │
             ▼
┌──────────────────────────────────────────┐
│  SubInventario (Tabla)                   │
│  ├─ id: 1                                │
│  ├─ fecha_subinventario: 2025-03-20      │
│  ├─ estado: "activo"                     │
│  ├─ descripcion: "Día de venta - Evento" │
│  └─ usuario: "Admin"                     │
└────────────┬─────────────────────────────┘
             │
             ▼
┌──────────────────────────────────────────┐
│  Asignar Libros al Sub-Inventario        │
│  (Pivot Table: subinventario_libro)      │
│  ├─ subinventario_id: 1                  │
│  ├─ libro_id: 5 (Biblia)                 │
│  └─ cantidad: 50 unidades                │
│  ├─ libro_id: 12 (Himnario)              │
│  └─ cantidad: 100 unidades               │
└──────────────────────────────────────────┘
```

### 2️⃣ IMPACTO EN STOCK DEL LIBRO

Cuando se asignan libros al sub-inventario:

```
ANTES DE ASIGNAR:
┌─────────────────────────────────────┐
│ Libro: "Biblia"                     │
│ stock: 200                          │
│ stock_subinventario: 0              │
│ Stock Disponible: 200               │
└─────────────────────────────────────┘

ASIGNAR 50 AL SUBINVENTARIO #1:
┌──────────────────────────────────────┐
│ Operación:                           │
│ libro.decrement('stock', 50)         │
│ libro.increment('stock_subinv...', 50)│
└──────────────────────────────────────┘

DESPUÉS DE ASIGNAR:
┌─────────────────────────────────────┐
│ Libro: "Biblia"                     │
│ stock: 150                          │  ← Restado
│ stock_subinventario: 50             │  ← Agregado
│ Stock Disponible: 150               │
│ En Sub-Inventarios: 50              │
└─────────────────────────────────────┘
```

---

## 💳 Proceso de Venta

### PASO 1: CREAR VENTA
```php
// Usuario selecciona tipo de inventario:
// Opción A: Inventario General
// Opción B: Sub-Inventario (selecciona cuál)

$venta = Venta::create([
    'fecha_venta' => '2025-03-20',
    'tipo_pago' => 'contado',
    'tipo_inventario' => 'subinventario',    // ← TIPO
    'subinventario_id' => 1,                 // ← CUÁL SUB-INV
    'descuento_global' => 0,
    'estado' => 'completada',
]);
```

### PASO 2: VALIDAR STOCK

#### A) Si es del INVENTARIO GENERAL:
```php
$libro = Libro::find($libroId);

if ($libro->stock < $cantidad) {
    return error("Stock insuficiente en inventario general");
}
```

#### B) Si es del SUB-INVENTARIO:
```php
$subinventario = SubInventario::with('libros')->find(1);
$libroEnSub = $subinventario->libros->firstWhere('id', $libroId);

// Validación 1: ¿Está el libro en el sub-inventario?
if (!$libroEnSub) {
    return error("El libro no está en este sub-inventario");
}

// Validación 2: ¿Hay cantidad suficiente?
if ($libroEnSub->pivot->cantidad < $cantidad) {
    return error("Cantidad insuficiente en sub-inventario");
}
```

### PASO 3: CREAR MOVIMIENTO

Se registra en tabla `movimientos`:

```php
Movimiento::create([
    'libro_id' => $libroId,
    'venta_id' => $ventaId,
    'tipo_movimiento' => 'salida',
    'tipo_salida' => 'venta',
    'cantidad' => 25,
    'precio_unitario' => 15.50,
    'descuento' => 5,
    'fecha' => '2025-03-20',
    'observaciones' => 'Venta #1 - SubInv #1',  // Indica origen
    'usuario' => 'Vendedor1',
]);
```

### PASO 4: ACTUALIZAR STOCK

#### A) Si es del INVENTARIO GENERAL:
```php
// Solo decrementar stock general
$libro->decrement('stock', 25);

// RESULTADO:
// stock: 175 (200 - 25)
// stock_subinventario: 0
```

#### B) Si es del SUB-INVENTARIO:
```php
// 1. Decrementar stock general (del inventario total)
$libro->decrement('stock', 25);

// 2. Actualizar cantidad en la tabla pivot (subinventario_libro)
$subinventario->libros()->updateExistingPivot($libroId, [
    'cantidad' => $cantidadActual - 25  // Ej: 50 - 25 = 25
]);

// 3. Decrementar stock_subinventario del libro
$libro->decrement('stock_subinventario', 25);

// RESULTADO PARA "Biblia":
// stock: 125 (150 - 25)
// stock_subinventario: 25 (50 - 25)
// En SubInv #1: 25 (50 - 25)
```

---

## 📊 Cálculo de Stock

### Fórmula de Stock Disponible

```
Stock Disponible = stock - stock_apartado - stock_subinventario

EJEMPLO:
Libro: "Himnario"
  stock: 1000                    # Total en el sistema
  stock_apartado: 100            # Reservado en apartados
  stock_subinventario: 250       # Distribuido en sub-inventarios
  
Stock General Disponible = 1000 - 100 - 250 = 650
```

### Estados de Stock por Ubicación

```
┌───────────────────────────────────────────────────────────┐
│ LIBRO: "Biblia" (Total en Sistema: 200)                  │
├───────────────────────────────────────────────────────────┤
│                                                            │
│  INVENTARIO GENERAL (Oficina Central):                   │
│  ├─ Disponible: 150                                      │
│  └─ (200 - 50 en SubInv#1)                               │
│                                                            │
│  SUB-INVENTARIO #1 (Punto de Venta):                     │
│  ├─ Asignado: 50                                         │
│  ├─ Vendido: 10                                          │
│  └─ Disponible en SubInv: 40                             │
│                                                            │
│  RESERVAS/APARTADOS:                                     │
│  └─ Reservado: 0                                         │
│                                                            │
│  TOTAL CONTABILIZADO: 150 + 40 + 0 = 190 ✓              │
│  (El libro se ve distribuido en diferentes lugares)      │
│                                                            │
└───────────────────────────────────────────────────────────┘
```

---

## 🎯 Casos de Uso

### CASO 1: Venta del Sub-Inventario (Punto de Venta)

```
ESCENARIO:
Un vendedor está en un evento. Vende 10 Biblias desde el 
Sub-Inventario #1 (que tiene 50 Biblias asignadas).

PASO A PASO:
1. Vendedor selecciona: "Vender desde Sub-Inventario #1"
2. Vendedor agrega: 10 Biblias a la venta
3. Sistema valida: ¿Hay 10 en SubInv #1? Sí ✓
4. Se crea la venta y el movimiento
5. Stock se actualiza:

   ANTES:
   ├─ stock: 200
   ├─ stock_subinventario: 50 (en SubInv #1)
   └─ Disponible en SubInv: 50

   DESPUÉS:
   ├─ stock: 190 (200 - 10)
   ├─ stock_subinventario: 40 (50 - 10)
   └─ Disponible en SubInv: 40

6. Venta registrada y pagada ✓
```

### CASO 2: Venta del Inventario General

```
ESCENARIO:
Un cliente en la oficina compra 5 Biblias del inventario general
(sin sub-inventarios).

PASO A PASO:
1. Vendedor selecciona: "Vender desde Inventario General"
2. Vendedor agrega: 5 Biblias a la venta
3. Sistema valida: ¿Hay 5 disponibles? 
   Disponible = 150 - 0 - 0 = 150 ✓
4. Se crea la venta
5. Stock se actualiza:

   ANTES:
   ├─ stock: 150
   ├─ stock_subinventario: 50
   └─ Disponible: 150

   DESPUÉS:
   ├─ stock: 145 (150 - 5)
   ├─ stock_subinventario: 50 (sin cambios)
   └─ Disponible: 145
```

### CASO 3: Cancelación de Venta desde Sub-Inventario

```
ESCENARIO:
El cliente devuelve la venta de 10 Biblias que compró del SubInv #1.

OPERACIÓN:
Sistema debe devolver los 10 libros al sub-inventario.

CÓDIGO EN VentaController.php (cancelar método):
─────────────────────────────────────────────────
foreach ($venta->movimientos as $movimiento) {
    $libro = $movimiento->libro;
    
    // 1. Incrementar stock general
    $libro->increment('stock', $movimiento->cantidad); // +10
    
    // 2. Verificar si fue de sub-inventario
    if (preg_match('/SubInv #(\d+)/', $movimiento->observaciones, $matches)) {
        $subinventarioId = $matches[1];
        $subinventario = SubInventario::find($subinventarioId);
        
        // 3. Restaurar cantidad en sub-inventario
        $libroEnSub = $subinventario->libros()->where('libro_id', $libro->id)->first();
        if ($libroEnSub) {
            $subinventario->libros()->updateExistingPivot($libro->id, [
                'cantidad' => $libroEnSub->pivot->cantidad + 10
            ]);
        }
        
        // 4. Incrementar stock_subinventario
        $libro->increment('stock_subinventario', 10);
    }
}

RESULTADO:
├─ stock: 155 (145 + 10)
├─ stock_subinventario: 50 (40 + 10)
└─ SubInv #1: 50 (40 + 10)
```

### CASO 4: Editar Venta desde Sub-Inventario

```
ESCENARIO:
Venta original: 20 Biblias del SubInv #1
Se edita a: 15 Biblias (5 menos)

LÓGICA EN VentaController.php (update método):
─────────────────────────────────────────────

// 1. Obtener cantidad anterior
$cantidadAnterior = 20;
$cantidadNueva = 15;
$diferencia = 5;  // Restamos 5

// 2. Si es menor, devolver las 5 al sub-inventario
if ($cantidadNueva < $cantidadAnterior) {
    $subinventario->libros()->updateExistingPivot($libroId, [
        'cantidad' => $cantidadActual + 5  // Devolver 5
    ]);
    
    // Incrementar stock_subinventario
    $libro->increment('stock_subinventario', 5);
    
    // NO incrementar stock general (se disminuyó menos)
    $libro->decrement('stock', 5);
}

// 3. Si es mayor, reducir del sub-inventario
if ($cantidadNueva > $cantidadAnterior) {
    // Validar disponibilidad
    if ($cantidadDisponibleEnSub < diferencia) {
        return error("No hay suficientes libros en el sub-inventario");
    }
    
    $subinventario->libros()->updateExistingPivot($libroId, [
        'cantidad' => $cantidadActual - 5  // Tomar 5 más
    ]);
    
    $libro->decrement('stock_subinventario', 5);
    $libro->decrement('stock', 5);
}
```

---

## 📈 Diagramas

### Diagrama 1: Flujo de Venta desde Sub-Inventario

```
┌─────────────────────────────────────────────────────────────────┐
│                    CREAR VENTA                                  │
│                                                                 │
│  1. Usuario selecciona tipo: SUB-INVENTARIO                     │
│  2. Usuario selecciona: Sub-Inventario #1                       │
│  3. Usuario agrega: 10 Biblias (cantidad)                       │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
        ┌─────────────────────────────┐
        │ VALIDACIÓN DE STOCK         │
        │ ¿10 <= 50 en SubInv #1?     │
        │ Sí ✓                        │
        └────────────┬────────────────┘
                     │
                     ▼
        ┌─────────────────────────────┐
        │ CREAR MOVIMIENTO DE SALIDA  │
        │ observaciones: "SubInv #1"  │
        └────────────┬────────────────┘
                     │
                     ▼
        ┌──────────────────────────────────────┐
        │ ACTUALIZAR STOCK DEL LIBRO           │
        │ 1. stock -= 10                       │
        │ 2. stock_subinventario -= 10         │
        │ 3. pivot.cantidad -= 10              │
        └────────────┬─────────────────────────┘
                     │
                     ▼
        ┌──────────────────────────────────────┐
        │ VENTA COMPLETADA ✓                   │
        │ SubInv #1: 50 → 40                   │
        │ Stock general: 200 → 190             │
        └──────────────────────────────────────┘
```

### Diagrama 2: Ciclo de Vida del Sub-Inventario

```
┌──────────────────┐
│   CREADO         │
│ (estado: activo) │
└────────┬─────────┘
         │
         ├─────────────────────┐
         │                     │
         ▼                     ▼
    ┌─────────────┐    ┌─────────────────┐
    │ VENDIENDO   │    │ CANCELADO       │
    │ (activo)    │    │ (cancelado)     │
    │             │    │ Stock devuelto  │
    │ Ventas 1..N │    └─────────────────┘
    └────┬────────┘
         │
         ▼
    ┌──────────────┐
    │ COMPLETADO   │
    │ (completado) │
    │ Todo vendido │
    └──────────────┘
```

### Diagrama 3: Tabla Pivot (subinventario_libro)

```
┌────────────────────────────────────────────────────────┐
│         TABLA: subinventario_libro                     │
├────────────────────────────────────────────────────────┤
│ ID │ subinv_id │ libro_id │ cantidad │ created_at    │
├────┼───────────┼──────────┼──────────┼───────────────┤
│ 1  │     1     │    5     │    50    │ 2025-03-20    │
│ 2  │     1     │   12     │   100    │ 2025-03-20    │
│ 3  │     1     │   23     │    75    │ 2025-03-20    │
│ 4  │     2     │    5     │    30    │ 2025-03-20    │
│ 5  │     2     │   12     │    60    │ 2025-03-20    │
└────┴───────────┴──────────┴──────────┴───────────────┘

EJEMPLO:
SubInv #1 tiene:
  • Biblia (ID 5): 50 unidades
  • Himnario (ID 12): 100 unidades
  • Folleto (ID 23): 75 unidades

SubInv #2 tiene:
  • Biblia (ID 5): 30 unidades
  • Himnario (ID 12): 60 unidades
```

---

## 🔧 Métodos Clave en Código

### VentaController.php

#### Método: `create()`
- Carga sub-inventarios activos con sus libros
- Prepara los datos para mostrar en el formulario de venta

#### Método: `store()`
- **Valida tipo de inventario** (general vs sub-inventario)
- **Si es sub-inventario:**
  - Obtiene el sub-inventario
  - Valida que el libro esté en el sub-inventario
  - Valida cantidad disponible en el sub-inventario
  - Crea el movimiento con observación "SubInv #ID"
  - Decrementa stock, stock_subinventario y cantidad en pivot

#### Método: `cancelar()`
- Restaura stock general
- Si detecta "SubInv #X" en observaciones:
  - Incrementa cantidad en pivot
  - Incrementa stock_subinventario

#### Método: `update()`
- Recalcula stock basándose en cambios
- Si fue de sub-inventario, actualiza pivot

### SubInventarioController.php

#### Método: `agregarLibroAlSubinventario()`
- Verifica si el libro ya existe en el sub-inventario
- Si existe: incrementa cantidad en pivot
- Si no existe: agrega nuevo con attach()
- Decrementa stock general e incrementa stock_subinventario

---

## ⚠️ Casos Especiales

### Venta a Plazos desde Sub-Inventario

```php
if ($esAPLazos) {
    // No se descuenta stock inmediatamente
    // Se crea un apartado con tipo_inventario = 'subinventario'
    // Stock se reserva en stock_apartado
    // Se descuenta de stock al completar el pago
}
```

### App Móvil - Venta desde Sub-Inventario (API)

```php
Route: POST /api/ventas-movil
Body: {
    subinventario_id: 1,
    cod_congregante: "14279",
    libros: [
        { libro_id: 5, cantidad: 10 },
        { libro_id: 12, cantidad: 5 }
    ]
}

Validaciones:
1. Usuario tiene acceso al Sub-Inventario
2. Libro está en Sub-Inventario
3. Cantidad disponible en SubInv
4. Sub-Inventario está activo
```

---

## 📝 Resumen

| Aspecto | General | Sub-Inventario |
|---------|---------|----------------|
| Stock Base | `stock` | `stock` |
| Reservado | `stock_apartado` | `stock_apartado` |
| Distribuido | Ninguno | `stock_subinventario` |
| Disponible | `stock - stock_apartado - stock_subinventario` | Cantidad en pivot |
| Validación | Por campo `stock` | Por campo pivot `cantidad` |
| Descuento | Decrementa solo `stock` | Decrementa `stock`, `stock_subinventario`, y pivot |
| Devolución | Incrementa `stock` | Incrementa `stock`, `stock_subinventario`, y pivot |
| Observaciones | "Venta #ID" | "Venta #ID - SubInv #ID" |

---

## 🚀 Conclusión

El sistema de sub-inventarios permite:

1. ✅ **Separación de puntos de venta**: Cada sub-inventario tiene libros específicos
2. ✅ **Control de stock granular**: Saber exactamente dónde está cada libro
3. ✅ **Ventas paralelas**: Múltiples sub-inventarios vendiendo simultáneamente
4. ✅ **Cancelaciones inteligentes**: Devuelve al lugar correcto (sub-inventario o general)
5. ✅ **Auditoría completa**: Los movimientos indican origen "SubInv #X"
