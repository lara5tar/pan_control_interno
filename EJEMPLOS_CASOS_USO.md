# 📊 EJEMPLOS: Casos de uso

## Ejemplo 1: Agregar nuevos libros (Caso más común)

**Cliente quiere agregar 10 nuevos libros al sub-inventario**

### Excel que prepara:

```
Código de Barras      | Cantidad
978-3-16-148410       | 2
978-3-16-148411       | 3
978-3-16-148412       | 1
978-3-16-148413       | 5
978-3-16-148414       | 4
```

### Resultado:
✅ Se agregan 5 libros diferentes con sus cantidades correctas

---

## Ejemplo 2: Actualizar cantidades (Recargas)

**Cliente ya tiene algunos libros pero necesita agregarles más**

### Situación actual:
- Libro "La Biblia Roja" (código: 978-001): 5 unidades
- Libro "Estudio de La Biblia" (código: 978-002): 3 unidades

### Excel que prepara:

```
Código de Barras      | Cantidad
978-001               | 3
978-002               | 5
```

### Resultado:
✅ El sistema suma automáticamente:
- "La Biblia Roja": 5 + 3 = **8 unidades**
- "Estudio de La Biblia": 3 + 5 = **8 unidades**

---

## Ejemplo 3: Mezcla (Nuevos + Actualizaciones)

**Cliente quiere agregar libros nuevos y recargar otros que ya tiene**

### Excel que prepara:

```
Código de Barras      | Cantidad
978-001               | 2          ← Actualización (ya existe)
978-002               | 3          ← Actualización (ya existe)
978-003               | 5          ← Nuevo
978-004               | 2          ← Nuevo
```

### Resultado:
✅ El sistema:
- Suma 2 más al libro 978-001
- Suma 3 más al libro 978-002
- Agrega nuevo el 978-003 con 5 unidades
- Agrega nuevo el 978-004 con 2 unidades

---

## Ejemplo 4: Gran volumen (100+ libros)

**Importar reabastecimiento completo**

### Caso real:
```
Código de Barras      | Cantidad
978-3-16-148410       | 10
978-3-16-148411       | 15
978-3-16-148412       | 8
...
978-3-16-999999       | 12
```

### Tiempo:
- ⏱️ Preparar: 5 minutos
- ⏱️ Cargar: 30 segundos
- **Total: 5.5 minutos para 100+ libros**

vs.

- ❌ Manual uno por uno: 3-4 horas

---

## Ejemplo 5: Archivo con errores

**Cliente carga un archivo con algunos datos incorrectos**

### Excel que prepara:

```
Código de Barras      | Cantidad
978-001               | 5          ← ✅ Correcto
978-002               | 3          ← ✅ Correcto
XXXXX                 | 5          ← ❌ Código no existe
978-003               | 0          ← ❌ Cantidad inválida
978-004               | 2          ← ✅ Correcto
```

### Resultado:
✅ 3 libros se importan correctamente  
❌ 2 errores reportados:
```
Fila 4: Libro con código 'XXXXX' no encontrado
Fila 5: Cantidad inválida (0)
```

**Cliente corrige y vuelve a intentar.**

---

## Ejemplo 6: Caso con stock insuficiente

**Cliente intenta agregar más libros de los que hay disponibles**

### Situación:
- Inventario general tiene: "Biblia" con solo 2 unidades

### Excel que prepara:

```
Código de Barras      | Cantidad
978-BIBLIA            | 10        ← Pero solo hay 2 disponibles
```

### Resultado:
❌ Error reportado:
```
Fila 2: Stock insuficiente para 'Biblia' (disponible: 2)
```

**Cliente puede:**
- Reducir cantidad a 2 y reintentar
- Esperar a reabastecimiento de inventario

---

## Ejemplo 7: Uso avanzado (Múltiples sub-inventarios)

**Mismo Excel se usa para actualizar varios sub-inventarios**

### Semana 1: Importa a Sub-Inventario #1
```
Código de Barras      | Cantidad
978-001               | 5
978-002               | 3
978-003               | 2
```

### Semana 2: Reutiliza el Excel (con ajustes) para Sub-Inventario #2
```
Código de Barras      | Cantidad
978-001               | 8         ← Diferentes cantidades
978-002               | 2
978-004               | 10        ← Otros libros
```

✅ Muy flexible y reutilizable

---

## 🎯 Formato correcto (Checklist)

Antes de cargar, verifica:

- [ ] Archivo es .xlsx, .xls o .csv
- [ ] Columna A: Códigos de barras
- [ ] Columna B: Cantidades
- [ ] Primera fila: Encabezados (se ignora)
- [ ] Sin celdas vacías en datos
- [ ] Códigos válidos
- [ ] Cantidades son números > 0
- [ ] Sub-inventario está activo

---

## ❌ Errores comunes

### Error 1: Código de barras incorrecto
```
Excel: 978-3-16-148410
Real:  978-3-16-148410 (tiene espacios)
```
**Solución:** Usa la plantilla descargable que tiene los códigos correctos

### Error 2: Cantidad cero
```
Código de Barras | Cantidad
978-001          | 0        ← No se puede
```
**Solución:** Usa números > 0

### Error 3: Formato de archivo
```
Archivo guardado como .txt en lugar de .xlsx
```
**Solución:** Guarda como Excel o usa la plantilla

### Error 4: Sub-inventario no activo
```
Sub-inventario estado: "Completado"
```
**Solución:** Solo puedes importar en sub-inventarios "activos"

---

## ✨ Tips pro

### Tip 1: Descarga periódicamente
La plantilla siempre tiene:
- Libros actualizados
- Códigos correctos
- Formato correcto

### Tip 2: Mantén respaldos
Guarda copias de Excel que uses:
```
📁 Importaciones_SubInventarios/
   ├─ 2024-01-15_Sub_Inv_1.xlsx
   ├─ 2024-02-20_Sub_Inv_2.xlsx
   └─ 2024-03-10_Sub_Inv_1_Recarga.xlsx
```

### Tip 3: Agrupa por lotes
Es más fácil:
- 5 importaciones de 20 libros
- Que 1 importación de 100 libros

### Tip 4: Revisa dos veces
Antes de "Importar Libros":
- Verifica códigos
- Verifica cantidades
- Verifica disponibilidad

---

## 📈 Comparativa: Tiempo

| Cantidad | Método Manual | Método Excel | Ahorro |
|----------|---------------|--------------|--------|
| 10 libros | 5 min | 1 min | 80% |
| 50 libros | 25 min | 2 min | 92% |
| 100 libros | 50 min | 3 min | 94% |
| 500 libros | 4 horas | 10 min | 96% |

---

**¡Los ejemplos muestran cómo usar esta potente funcionalidad!** 🚀
