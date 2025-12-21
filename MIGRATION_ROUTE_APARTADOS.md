# Ruta de Migración de Apartados

## 📋 Descripción

Esta ruta permite ejecutar las migraciones de apartados directamente desde el navegador en el hosting, sin necesidad de acceso SSH.

## 🔐 Seguridad

La ruta está protegida con una clave secreta para evitar accesos no autorizados.

## 🌐 URL de Acceso

```
https://tu-dominio.com/run-apartados-migration?key=pan2025secure
```

**⚠️ IMPORTANTE: Cambia la clave `pan2025secure` por una clave segura en producción**

## 🗂️ Migraciones que se ejecutan

1. **2025_11_24_020150_create_apartados_table.php**
   - Crea la tabla `apartados` con todos los campos necesarios

2. **2025_11_24_020235_add_stock_apartado_to_libros_table.php**
   - Agrega la columna `stock_apartado` a la tabla `libros`

3. **2025_12_21_062800_create_apartados_sistema_table.php**
   - Crea la tabla `apartado_detalles` para los detalles de cada apartado
   - Crea la tabla `abonos` para registrar los pagos

4. **2025_12_21_062801_add_apartado_id_to_ventas_table.php**
   - Agrega la columna `apartado_id` a la tabla `ventas` para vincular ventas generadas desde apartados

## ✅ Respuesta Exitosa

```json
{
  "success": true,
  "message": "Migraciones de apartados ejecutadas correctamente",
  "migrations": [
    "2025_11_24_020150_create_apartados_table.php",
    "2025_11_24_020235_add_stock_apartado_to_libros_table.php",
    "2025_12_21_062800_create_apartados_sistema_table.php",
    "2025_12_21_062801_add_apartado_id_to_ventas_table.php"
  ],
  "output": "Detalles de cada migración..."
}
```

## ❌ Errores Comunes

### Error 403 - Acceso no autorizado
```json
{
  "message": "Acceso no autorizado"
}
```
**Solución:** Verifica que estás usando la clave correcta en el parámetro `key`

### Error 500 - Error en la migración
```json
{
  "success": false,
  "message": "Error al ejecutar las migraciones",
  "error": "Detalle del error...",
  "trace": "Stack trace completo..."
}
```
**Solución:** Revisa el mensaje de error y verifica que:
- La base de datos esté accesible
- Las tablas no existan previamente
- Los permisos de la base de datos sean correctos

## 🔧 Cambiar la Clave de Seguridad

Para cambiar la clave secreta, edita el archivo `routes/web.php`:

```php
// Busca esta línea:
if ($key !== 'pan2025secure') {

// Cámbiala por tu clave segura:
if ($key !== 'TU_CLAVE_SUPER_SEGURA_AQUI') {
```

## 📝 Notas

- Esta ruta puede ejecutarse múltiples veces sin problemas
- Si las migraciones ya fueron ejecutadas, Laravel detectará que no hay nada que migrar
- Es recomendable **eliminar o comentar esta ruta después de usarla en producción** por seguridad

## 🚨 Seguridad en Producción

**Después de ejecutar las migraciones en el hosting:**

1. Comenta o elimina la ruta en `routes/web.php`
2. O cambia la clave a algo extremadamente complejo
3. Considera agregar restricciones de IP si es posible

```php
// Para deshabilitar temporalmente:
/*
Route::get('/run-apartados-migration', function () {
    // ... código de la ruta
})->name('migration.apartados');
*/
```
