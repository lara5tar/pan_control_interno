# Módulo de Apartados de Inventario

## Descripción

El módulo de Apartados de Inventario permite reservar una cantidad específica de libros del inventario para venderlos en un día determinado. Esta funcionalidad es útil cuando el usuario necesita separar inventario para eventos de venta específicos, como ferias de libros, ventas especiales, etc.

## Características Principales

### 1. Creación de Apartados
- Permite seleccionar qué libros y cuántas unidades apartar para un día específico
- Valida que haya stock disponible antes de apartar
- Reduce el stock disponible automáticamente al crear el apartado
- Mantiene un registro separado de "stock apartado" por libro

### 2. Estados de Apartados
- **Activo**: El apartado está vigente y el inventario está reservado
- **Completado**: Se vendió todo el inventario apartado (se reduce el stock_apartado)
- **Cancelado**: Se canceló el apartado y el inventario se devuelve al stock disponible

### 3. Gestión de Stock
El sistema maneja dos tipos de stock:
- **Stock Total**: La cantidad total de libros en inventario
- **Stock Apartado**: La cantidad de libros reservados para apartados activos
- **Stock Disponible**: `stock - stock_apartado` (disponible para ventas normales)

### 4. Operaciones Disponibles

#### Crear Apartado
1. Ir a "Apartados" en el menú de navegación
2. Clic en "Nuevo Apartado"
3. Seleccionar fecha de apartado y agregar descripción (opcional)
4. Agregar libros y cantidades a apartar
5. El sistema valida que haya stock disponible
6. Al guardar, se reduce el stock disponible y aumenta el stock apartado

#### Editar Apartado
- Solo se pueden editar apartados con estado "Activo"
- Al editar, se devuelve el stock de los libros anteriores
- Se valida nuevamente el stock disponible para los nuevos libros
- Se actualiza el stock apartado según los cambios

#### Completar Apartado
- Indica que se vendió todo el inventario apartado
- Reduce el stock_apartado de cada libro
- Cambia el estado a "Completado"
- No se puede revertir esta acción

#### Cancelar Apartado
- Devuelve todo el inventario apartado al stock disponible
- Cambia el estado a "Cancelado"
- Se puede cancelar desde estado "Activo"

#### Devolución Parcial
- Permite devolver parte del inventario apartado antes de completar
- Útil cuando no se vendió todo lo apartado
- Actualiza las cantidades en el apartado
- Si se devuelve todo, cancela automáticamente el apartado

### 5. Integración con Ventas

El sistema de ventas ahora considera el stock apartado:
- Al crear una venta, solo muestra libros con stock disponible (stock - stock_apartado > 0)
- Valida que la cantidad a vender no exceda el stock disponible
- Esto asegura que las ventas normales no puedan usar el inventario apartado

## Estructura de Base de Datos

### Tabla: `apartados`
```sql
- id (PK)
- fecha_apartado (DATE)
- descripcion (VARCHAR, nullable)
- estado (ENUM: activo, completado, cancelado)
- usuario (VARCHAR, nullable)
- observaciones (TEXT, nullable)
- created_at
- updated_at
```

### Tabla: `apartado_libro` (Pivote)
```sql
- id (PK)
- apartado_id (FK -> apartados.id)
- libro_id (FK -> libros.id)
- cantidad (INTEGER)
- created_at
- updated_at
```

### Modificación en tabla: `libros`
```sql
+ stock_apartado (INTEGER, default: 0)
```

## Flujo de Trabajo Típico

### Escenario: Venta en Feria de Libros

1. **Día antes del evento**
   - Crear apartado con fecha del evento
   - Seleccionar 50 unidades del libro "Biblia para niños"
   - Seleccionar 30 unidades del libro "Devocional diario"
   - Guardar apartado (estado: Activo)
   - El stock disponible se reduce automáticamente

2. **Durante el evento**
   - El inventario está reservado para el evento
   - Las ventas normales no pueden acceder a ese stock
   - Se pueden hacer devoluciones parciales si no se lleva algo

3. **Después del evento**
   - Si se vendió todo: Marcar apartado como "Completado"
   - Si sobró inventario: Hacer devolución parcial primero
   - El sistema actualiza el stock_apartado automáticamente

## Reportes y Consultas

### Apartados Activos
Muestra todos los apartados vigentes con su inventario reservado

### Apartados por Fecha
Filtra apartados por fecha específica para ver qué está apartado cada día

### Stock por Libro
En la vista de inventario, cada libro muestra:
- Stock total
- Stock apartado
- Stock disponible

## Validaciones Importantes

1. **Al crear apartado**: Verifica que `stock - stock_apartado >= cantidad_a_apartar`
2. **Al editar apartado**: Solo permite editar apartados activos
3. **Al completar apartado**: Solo permite completar apartados activos
4. **Al crear venta**: Solo permite vender del stock disponible (no apartado)
5. **Devolución parcial**: La cantidad devuelta no puede ser mayor a la apartada

## Permisos y Seguridad

- Solo usuarios autenticados pueden gestionar apartados
- Los apartados completados no se pueden eliminar (mantiene historial)
- Los apartados cancelados se pueden eliminar
- Cada apartado registra el usuario que lo creó

## Casos de Uso Adicionales

### 1. Venta por Consignación
Apartar inventario que se enviará a otro local para vender

### 2. Eventos Especiales
Reservar inventario para conferencias, seminarios, etc.

### 3. Preventa
Apartar inventario para órdenes prevendidas

### 4. Inventario de Exhibición
Separar libros que estarán en exhibición pero no disponibles para venta inmediata

## Mantenimiento

### Limpieza de Apartados Antiguos
Se recomienda revisar periódicamente:
- Apartados activos con fechas pasadas
- Apartados cancelados muy antiguos (se pueden eliminar)

### Auditoría
El sistema mantiene registro de:
- Fecha de creación de cada apartado
- Usuario que creó el apartado
- Cambios de estado (a través de timestamps)

## Resolución de Problemas

### Problema: Stock negativo o inconsistente
**Solución**: Verificar que no haya apartados activos huérfanos. Revisar la tabla `apartado_libro` y `libros.stock_apartado`

### Problema: No se puede crear venta (sin stock)
**Causa**: Posiblemente todo el stock está apartado
**Solución**: Revisar apartados activos y devolver inventario si es necesario

### Problema: Apartado no se puede completar
**Causa**: El apartado no está en estado "Activo"
**Solución**: Solo se pueden completar apartados activos

## Mejoras Futuras Sugeridas

1. Notificaciones automáticas cuando se acerca la fecha de un apartado
2. Reportes de utilización de apartados (cuánto se vendió vs. cuánto se apartó)
3. Historial de cambios en apartados
4. Integración con sistema de órdenes de compra
5. Apartados recurrentes para eventos periódicos
