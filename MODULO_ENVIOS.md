# Módulo de Envíos - Pan de Vida Control Interno

## 📦 Descripción
Módulo completo para gestionar envíos a FedEx, permitiendo asociar múltiples ventas a un envío, controlar costos, estados y mantener comprobantes digitalizados.

## ✅ Características Implementadas

### 1. **Base de Datos**
- ✅ Tabla `envios` con campos:
  - `id`: ID único del envío
  - `guia`: Número de guía de FedEx o referencia
  - `fecha_envio`: Fecha del envío
  - `monto_a_pagar`: Monto a pagar a FedEx
  - `comprobante`: Archivo de factura/comprobante (PDF, JPG, PNG)
  - `notas`: Notas adicionales
  - `estado`: pendiente | en_transito | entregado | cancelado
  - `usuario`: Usuario que registró el envío
  
- ✅ Tabla pivote `envio_venta` para relación muchos a muchos:
  - Un envío puede tener múltiples ventas
  - Una venta puede estar en múltiples envíos

### 2. **Modelo Envio**
- ✅ Relaciones con Venta (belongsToMany)
- ✅ Métodos helper:
  - `getBadgeColor()`: Color del badge según estado
  - `getIcon()`: Icono según estado
  - `getEstadoLabel()`: Texto legible del estado
  - `calcularTotalVentas()`: Suma el total de ventas asociadas
  - `getCantidadVentasAttribute()`: Cantidad de ventas
  - `getTotalLibrosAttribute()`: Total de libros enviados

- ✅ Scopes de búsqueda:
  - `scopeEstado()`: Filtrar por estado
  - `scopeSearch()`: Búsqueda por ID o guía
  - `scopeEntreFechas()`: Rango de fechas
  - `scopePorMes()`: Por mes y año
  - `scopeHoy()`: Envíos del día
  - `scopeSemanaActual()`: Envíos de la semana
  - `scopeMesActual()`: Envíos del mes
  - `scopeConVenta()`: Envíos que contienen una venta específica

### 3. **Controlador EnvioController**
- ✅ CRUD completo (index, create, store, show, edit, update, destroy)
- ✅ Sistema de filtros avanzados:
  - Por venta específica
  - Por estado
  - Por rango de fechas
  - Por rango de montos
  - Búsqueda general
  - Ordenamiento personalizado
  
- ✅ Estadísticas en tiempo real:
  - Total de envíos
  - Monto total
  - Contadores por estado
  - Total de ventas asociadas
  
- ✅ Exportación de reportes:
  - Excel (con estadísticas y filtros)
  - PDF (formato landscape)
  
- ✅ Gestión de archivos:
  - Subida de comprobantes
  - Validación de formato (PDF, JPG, PNG)
  - Límite de 5MB
  - Eliminación automática al borrar envío

### 4. **Vistas Blade**

#### `envios/index.blade.php`
- ✅ Listado paginado de envíos
- ✅ Tarjetas de estadísticas
- ✅ Filtros avanzados en grid
- ✅ Tabla responsiva con estados coloreados
- ✅ Botones de exportación
- ✅ Diseño consistente con módulo de ventas

#### `envios/create.blade.php`
- ✅ Formulario completo con validación
- ✅ Selector múltiple de ventas con tabla interactiva
- ✅ Checkbox "Seleccionar todos"
- ✅ Contador de ventas seleccionadas
- ✅ Upload de comprobante
- ✅ Estados predefinidos

#### `envios/show.blade.php`
- ✅ Vista detallada del envío
- ✅ Información del envío en cards
- ✅ Resumen de costos
- ✅ Enlace para ver comprobante
- ✅ Tabla de ventas asociadas
- ✅ Tabla de libros agrupados por tipo
- ✅ Botones de acción (editar, eliminar)

#### `envios/edit.blade.php`
- ✅ Formulario de edición
- ✅ Valores prellenados
- ✅ Opción para reemplazar comprobante
- ✅ Modificar ventas asociadas
- ✅ Mismo diseño que create

#### `envios/pdf-report.blade.php`
- ✅ Plantilla para reportes PDF
- ✅ Estadísticas resumidas
- ✅ Tabla con todos los envíos
- ✅ Badges de estado coloreados
- ✅ Totales calculados

### 5. **Navegación**
- ✅ Enlace "Envíos" en navbar desktop
- ✅ Enlace "Envíos" en menú móvil
- ✅ Icono: `fas fa-shipping-fast`
- ✅ Resaltado activo cuando estás en el módulo

### 6. **Rutas**
```php
// CRUD completo
Route::resource('envios', EnvioController::class);

// Exportaciones
Route::get('/envios-export/excel', [EnvioController::class, 'exportExcel']);
Route::get('/envios-export/pdf', [EnvioController::class, 'exportPdf']);
```

## 🎨 Diseño Visual

El módulo sigue **exactamente el mismo diseño** que los módulos existentes:
- Paleta de colores consistente
- Componentes reutilizables (x-button, x-card, x-stat-card, etc.)
- Layout responsivo con Tailwind CSS
- Iconos Font Awesome 6
- Animaciones y transiciones suaves

## 🔄 Relaciones

### Envio → Ventas (Many to Many)
```php
$envio->ventas; // Colección de ventas
$envio->ventas->count(); // Cantidad
```

### Venta → Envios (Many to Many)
```php
$venta->envios; // Envíos que incluyen esta venta
```

## 📊 Casos de Uso

1. **Crear Envío**: 
   - Ir a Envíos → Nuevo Envío
   - Completar información (guía, fecha, monto)
   - Seleccionar una o más ventas
   - Opcionalmente subir comprobante
   - Guardar

2. **Ver Detalle**:
   - Información completa del envío
   - Ventas incluidas con sus totales
   - Libros agrupados por tipo
   - Descargar comprobante si existe

3. **Editar Envío**:
   - Modificar cualquier campo
   - Agregar o quitar ventas
   - Reemplazar comprobante

4. **Filtrar y Buscar**:
   - Por estado
   - Por rango de fechas
   - Por venta específica
   - Por guía o ID

5. **Reportes**:
   - Excel: Datos detallados con estadísticas
   - PDF: Formato imprimible con logo

## 📁 Estructura de Archivos

```
app/
├── Models/
│   └── Envio.php                          ✅ Nuevo
├── Http/Controllers/
│   └── EnvioController.php                ✅ Nuevo

database/migrations/
├── 2025_11_18_185239_create_envios_table.php           ✅ Nuevo
└── 2025_11_18_185309_create_envio_venta_table.php      ✅ Nuevo

resources/views/
├── envios/
│   ├── index.blade.php                    ✅ Nuevo
│   ├── create.blade.php                   ✅ Nuevo
│   ├── show.blade.php                     ✅ Nuevo
│   ├── edit.blade.php                     ✅ Nuevo
│   └── pdf-report.blade.php               ✅ Nuevo
└── components/
    └── navbar.blade.php                   ✅ Modificado

routes/
└── web.php                                ✅ Modificado

storage/app/public/
└── comprobantes/
    └── envios/                            ✅ Nuevo directorio
```

## ✨ Mejoras Implementadas

1. **Validación robusta**: Todos los campos validados en backend
2. **Seguridad**: Archivos limitados a tipos seguros y tamaño máximo
3. **UX mejorada**: 
   - Click en fila para seleccionar venta
   - Contador visual de selección
   - Select all funcional
4. **Responsive**: Funciona perfecto en móvil y desktop
5. **Consistencia**: 100% alineado con el diseño existente
6. **Performance**: Eager loading de relaciones para evitar N+1

## 🚀 Próximos Pasos Sugeridos (Opcional)

- [ ] Notificaciones por email al cambiar estado
- [ ] Dashboard con métricas de envíos
- [ ] Integración con API de FedEx para tracking
- [ ] Código QR en comprobante
- [ ] Historial de cambios de estado

## 📝 Notas Importantes

- Las migraciones ya fueron ejecutadas ✅
- Los comprobantes se guardan en `storage/app/public/comprobantes/envios/`
- Asegúrate de tener el symlink de storage: `php artisan storage:link`
- El módulo está completamente funcional y listo para usar

---

**Desarrollado siguiendo los estándares del proyecto Pan de Vida - Control Interno**
