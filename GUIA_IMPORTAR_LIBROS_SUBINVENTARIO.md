# 📊 Guía: Importar Libros en Lote al Sub-Inventario

## ¿Qué es?
Una nueva funcionalidad que permite agregar **múltiples libros a un sub-inventario de una sola vez** usando un archivo Excel. 

Ya no es necesario agregar los libros uno por uno. ¡Mucho más rápido y eficiente!

---

## 🚀 Pasos para usar

### **Paso 1: Acceder a la opción de importación**
1. Ve a **Sub-Inventarios** en el menú principal
2. Haz clic en el sub-inventario donde deseas agregar libros
3. En la sección "Libros en Sub-Inventario", haz clic en el botón **"Importar Libros"** (azul)

![Paso 1](./images/paso1.jpg)

### **Paso 2: Descargar la plantilla**
1. En la página de importación, haz clic en el botón **"Descargar Plantilla"**
2. Se descargará un archivo Excel con el formato correcto
3. Este archivo ya contiene algunos libros disponibles como ejemplo

![Paso 2](./images/paso2.jpg)

### **Paso 3: Completar los datos**
Abre el archivo Excel descargado y completa las columnas:

| Columna | Nombre | Descripción |
|---------|--------|-------------|
| **A** | Código de Barras | El código de barras del libro (ej: 978-3-16-148410) |
| **B** | Cantidad | La cantidad de unidades a agregar (ej: 5) |

**Ejemplo:**
```
Código de Barras    |  Cantidad
978-3-16-148410     |  5
978-3-16-148411     |  3
978-3-16-148412     |  2
978-3-16-148413     |  10
```

### **Paso 4: Cargar el archivo**
1. Regresa a la página de importación
2. Haz clic en el cuadro de selección de archivo
3. Selecciona el Excel que completaste
4. Haz clic en **"Importar Libros"** (verde)

![Paso 4](./images/paso4.jpg)

---

## ✅ Validaciones automáticas

El sistema verifica automáticamente que:

✓ El código de barras exista en el inventario general  
✓ La cantidad sea un número válido (no 0)  
✓ Haya suficiente stock disponible en el inventario general  
✓ El sub-inventario esté en estado "activo"  

---

## ⚠️ Importante

### **¿Qué pasa si un libro ya está en el sub-inventario?**
La cantidad se **suma automáticamente**. Por ejemplo:
- Si el libro ya tenía 5 unidades
- Y cargas 3 más
- El resultado será: 8 unidades

### **¿Qué pasa si hay errores?**
Se mostrarán los errores específicos por fila, y podrás corregirlos y volver a intentar:
- "Fila 5: Libro con código '123456' no encontrado"
- "Fila 7: Stock insuficiente para 'Nombre del Libro' (disponible: 2)"

### **¿Puedo usar CSV en lugar de Excel?**
Sí, pero recomendamos Excel para mayor compatibilidad.

---

## 🎯 Casos de uso

### **Caso 1: Agregar varios libros nuevos**
- Descarga la plantilla
- Completa con los códigos de barras de los libros que deseas
- Carga el archivo
- ¡Listo! Todos se agregan en 1 minuto

### **Caso 2: Aumentar la cantidad de libros existentes**
- Los libros que ya están en el sub-inventario se actualizarán
- La cantidad se sumará a la existente
- Perfecto para recargas

### **Caso 3: Mezclar libros nuevos y actualizaciones**
- En un solo archivo puedes agregar libros nuevos y aumentar la cantidad de otros
- El sistema se encarga de todo automáticamente

---

## 📋 Checklist antes de cargar

- [ ] El archivo es Excel (.xlsx) o CSV (.csv)
- [ ] Los códigos de barras existen en el inventario general
- [ ] Las cantidades son números válidos (mayor que 0)
- [ ] El sub-inventario está en estado "activo"
- [ ] Revisé que no falten datos en las columnas

---

## 🆘 Solucionar problemas

| Problema | Solución |
|----------|----------|
| "El archivo debe ser Excel o CSV" | Asegúrate de cargar un .xlsx, .xls o .csv |
| "Libro con código 'XXX' no encontrado" | Verifica el código de barras en el inventario general |
| "Stock insuficiente" | Revisa que haya suficientes unidades en el inventario general |
| "Cantidad inválida" | Usa solo números enteros (1, 2, 3, etc.) |
| El sub-inventario no deja importar | Verifica que el estado sea "activo" (no completado ni cancelado) |

---

## 💡 Consejos pro

1. **Usa la plantilla descargable** - Asegura el formato correcto
2. **Descarga periódicamente** - La plantilla siempre tiene la lista actualizada de libros disponibles
3. **Revisa dos veces** - Antes de cargar, verifica que los datos sean correctos
4. **Agrupa en tandas** - Es más fácil manejar 20 libros que 100 de una vez
5. **Mantén respaldos** - Guarda copias de tus archivos Excel

---

## 📞 ¿Necesitas ayuda?

Si tienes problemas:
1. Revisa este documento
2. Verifica que cumplas con todas las validaciones
3. Descarga nuevamente la plantilla (puede estar desactualizada)
4. Contacta al administrador si persiste el error

---

**¡Disfruta de la funcionalidad! Ahora cargar libros es mucho más rápido.** 🎉
