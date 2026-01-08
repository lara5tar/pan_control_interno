# 📋 Resumen de Implementación: Tracking de Origen de Ventas

## ✅ Objetivo Completado

Se implementó exitosamente el tracking del origen de las ventas en el sistema, permitiendo identificar si una venta se realizó desde el **Inventario General** o desde un **Subinventario** (punto de venta).

---

## 🗄️ Cambios en la Base de Datos

### Migration: `2026_01_07_071710_add_inventario_origen_to_ventas_table.php`

**Columnas agregadas a la tabla `ventas`:**

```php
$table->enum('tipo_inventario', ['general', 'subinventario'])->default('general');
$table->unsignedBigInteger('subinventario_id')->nullable();
$table->foreign('subinventario_id')->references('id')->on('subinventarios')->onDelete('set null');
```

- **`tipo_inventario`**: Enum que indica el origen ('general' o 'subinventario')
- **`subinventario_id`**: Foreign key opcional que apunta al subinventario específico cuando tipo_inventario='subinventario'

**Estado**: ✅ Ejecutada exitosamente

---

## 💻 Cambios en el Código

### 1. Modelo: `app/Models/Venta.php`

**Agregado a `$fillable`:**
```php
'tipo_inventario',
'subinventario_id',
```

**Nueva relación:**
```php
public function subinventario()
{
    return $this->belongsTo(SubInventario::class);
}
```

### 2. Controlador: `app/Http/Controllers/VentaController.php`

**En método `store()`:**

- **Validación de acceso**: Verifica que el usuario tenga acceso al subinventario seleccionado:
  ```php
  if ($validated['tipo_inventario'] === 'subinventario') {
      $codCongregante = session('codCongregante');
      $tieneAcceso = DB::table('subinventario_user')
          ->where('subinventario_id', $validated['subinventario_id'])
          ->where('cod_congregante', $codCongregante)
          ->exists();
      
      if (!$tieneAcceso) {
          return back()->with('error', 'No tienes acceso a este punto de venta');
      }
  }
  ```

- **Guardado de origen**: Se guardan `tipo_inventario` y `subinventario_id` al crear la venta:
  ```php
  $venta = Venta::create([
      // ... otros campos
      'tipo_inventario' => $validated['tipo_inventario'],
      'subinventario_id' => $validated['subinventario_id'] ?? null,
  ]);
  ```

### 3. Vista: `resources/views/ventas/show.blade.php`

**Sección agregada** (alrededor de la línea 249):
```blade
<div class="bg-white rounded-lg p-6 border border-gray-200">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-warehouse text-primary-500 mr-2"></i>
        Origen de Inventario
    </h3>
    @if($venta->tipo_inventario === 'subinventario' && $venta->subinventario)
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
            <i class="fas fa-store mr-2"></i>
            SubInventario #{{ $venta->subinventario->id }} - {{ $venta->subinventario->descripcion }}
        </span>
    @else
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
            <i class="fas fa-warehouse mr-2"></i>
            Inventario General
        </span>
    @endif
</div>
```

---

## 🧪 Tests Implementados

### Comando 1: `php artisan test:venta-tipo-inventario`
**Archivo:** `app/Console/Commands/TestVentaTipoInventario.php`

- Crea una venta desde el inventario general
- Verifica que `tipo_inventario = 'general'` y `subinventario_id = NULL`

**Resultado:** ✅ EXITOSO

```
ID:                  1
Tipo Inventario:     general
Subinventario ID:    NULL
```

### Comando 2: `php artisan test:venta-subinventario`
**Archivo:** `app/Console/Commands/TestVentaDesdeSubinventario.php`

- Crea una venta desde un subinventario
- Verifica que `tipo_inventario = 'subinventario'` y `subinventario_id` contiene el ID correcto

**Resultado:** ✅ EXITOSO

```
ID:                  2
Tipo Inventario:     subinventario
Subinventario ID:    1
Subinventario:       #1 - inventario test
```

### Verificación en Base de Datos

```sql
SELECT id, fecha_venta, tipo_inventario, subinventario_id FROM ventas;
```

| ID | Fecha | Tipo Inventario | Subinventario ID |
|----|-------|----------------|------------------|
| 1  | 2026-01-07 | general | NULL |
| 2  | 2026-01-07 | subinventario | 1 |
| 3  | 2026-01-07 | general | NULL |

✅ **Todos los datos se guardan correctamente**

---

## 🔐 Seguridad

**Control de acceso implementado:**

- Solo los usuarios asignados a un subinventario pueden crear ventas desde ese punto de venta
- La validación se realiza consultando la tabla `subinventario_user`
- Se compara el `cod_congregante` del usuario en sesión con los registros de la tabla

**Código de validación:**
```php
$tieneAcceso = DB::table('subinventario_user')
    ->where('subinventario_id', $validated['subinventario_id'])
    ->where('cod_congregante', session('codCongregante'))
    ->exists();
```

---

## 📊 Visualización

### En la Vista de Detalle de Venta

**Inventario General:**
```
┌─────────────────────────────┐
│ 🏭 Origen de Inventario     │
├─────────────────────────────┤
│ 🏭 Inventario General       │
│    (badge gris)              │
└─────────────────────────────┘
```

**Subinventario:**
```
┌─────────────────────────────────────────┐
│ 🏭 Origen de Inventario                 │
├─────────────────────────────────────────┤
│ 🏪 SubInventario #1 - inventario test  │
│    (badge morado)                        │
└─────────────────────────────────────────┘
```

---

## 🎯 Funcionalidades Completas

✅ **Columnas en base de datos**: tipo_inventario y subinventario_id
✅ **Formulario de venta**: Radio buttons para seleccionar tipo de inventario
✅ **Validación de acceso**: Usuario debe estar asignado al subinventario
✅ **Guardado correcto**: Ambos campos se persisten en la base de datos
✅ **Visualización**: Badge con color diferenciado según el origen
✅ **Relación de modelos**: Venta belongsTo SubInventario
✅ **Tests funcionales**: 2 comandos Artisan que verifican el comportamiento
✅ **Seguridad**: Control de acceso por cod_congregante

---

## 📝 Notas Importantes

1. **Valor por defecto**: Si no se especifica, `tipo_inventario` es 'general'
2. **Null safety**: `subinventario_id` es nullable y usa `onDelete('set null')`
3. **Eager loading**: En la vista se usa `->with('subinventario')` para evitar N+1 queries
4. **Backward compatible**: Ventas antiguas sin estos campos funcionan como 'general' con subinventario_id null

---

## 🚀 Cómo Usar

### Crear una venta desde Inventario General:
1. Ir a **Ventas > Nueva Venta**
2. Seleccionar **Inventario General** (radio button)
3. Llenar el formulario normalmente
4. Guardar

### Crear una venta desde un Subinventario:
1. Ir a **Ventas > Nueva Venta**
2. Seleccionar **Punto de Venta (Subinventario)** (radio button)
3. Elegir el subinventario del dropdown (solo aparecerán aquellos a los que el usuario tiene acceso)
4. Llenar el formulario
5. Guardar

**El sistema automáticamente guardará el origen de la venta.**

---

## ✅ Checklist de Implementación

- [x] Migración creada y ejecutada
- [x] Modelo actualizado (fillable + relación)
- [x] Controlador con validación de acceso
- [x] Vista con selección de tipo de inventario
- [x] Vista de detalle muestra el origen
- [x] Tests funcionales creados
- [x] Verificación en base de datos
- [x] Documentación completa

---

## 📅 Fecha de Implementación
**2026-01-07**

**Desarrollado por:** GitHub Copilot (Claude Sonnet 4.5)
