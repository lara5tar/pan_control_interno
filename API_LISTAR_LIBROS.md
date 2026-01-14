# API - Listar Todos los Libros

## 📋 Resumen Rápido

API para obtener la lista completa de libros con filtros, búsqueda y paginación. Ideal para listar el catálogo en tu app móvil y luego consultar disponibilidad específica.

---

## 🔗 Endpoint

```
GET /api/v1/libros
```

---

## 📥 Parámetros (todos opcionales)

| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| `con_stock` | boolean | Filtrar solo libros con stock > 0 | `con_stock=true` |
| `buscar` | string | Buscar por nombre del libro | `buscar=biblia` |
| `precio_min` | number | Precio mínimo | `precio_min=50` |
| `precio_max` | number | Precio máximo | `precio_max=200` |
| `ordenar` | string | Campo para ordenar: `nombre`, `precio`, `stock`, `created_at` | `ordenar=precio` |
| `direccion` | string | Dirección del orden: `asc`, `desc` | `direccion=desc` |
| `per_page` | integer | Resultados por página (máx 100) | `per_page=20` |
| `page` | integer | Número de página | `page=2` |

---

## 📤 Respuesta

### Ejemplo 1: Listar todos los libros (paginados)
```bash
curl "http://127.0.0.1:8000/api/v1/libros?per_page=5"
```

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nombre": "21 dias 1",
            "codigo_barras": null,
            "precio": 8,
            "stock": 1609,
            "stock_subinventario": 0,
            "stock_apartado": 0
        },
        {
            "id": 2,
            "nombre": "21 dias 2",
            "codigo_barras": null,
            "precio": 8,
            "stock": 2960,
            "stock_subinventario": 0,
            "stock_apartado": 0
        }
    ],
    "pagination": {
        "total": 183,
        "per_page": 5,
        "current_page": 1,
        "last_page": 37,
        "from": 1,
        "to": 5
    }
}
```

### Ejemplo 2: Solo libros con stock
```bash
curl "http://127.0.0.1:8000/api/v1/libros?con_stock=true&per_page=3"
```

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nombre": "21 dias 1",
            "codigo_barras": null,
            "precio": 8,
            "stock": 1609,
            "stock_subinventario": 0,
            "stock_apartado": 0
        }
    ],
    "pagination": {
        "total": 105,
        "per_page": 3,
        "current_page": 1,
        "last_page": 35
    }
}
```

### Ejemplo 3: Buscar por nombre
```bash
curl "http://127.0.0.1:8000/api/v1/libros?buscar=biblia"
```

### Ejemplo 4: Filtrar por rango de precio
```bash
curl "http://127.0.0.1:8000/api/v1/libros?precio_min=50&precio_max=200&ordenar=precio&direccion=asc"
```

---

## 📊 Estructura de la Respuesta

### Objeto Libro
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | integer | ID único del libro |
| `nombre` | string | Nombre del libro |
| `codigo_barras` | string\|null | Código de barras (si existe) |
| `precio` | number | Precio del libro |
| `stock` | integer | Stock en inventario general |
| `stock_subinventario` | integer | Stock total en todos los subinventarios |
| `stock_apartado` | integer | Stock reservado en apartados |

### Objeto Pagination
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `total` | integer | Total de libros (con filtros aplicados) |
| `per_page` | integer | Resultados por página |
| `current_page` | integer | Página actual |
| `last_page` | integer | Última página |
| `from` | integer | Índice del primer resultado |
| `to` | integer | Índice del último resultado |

---

## 💡 Flujo Completo en App Móvil

### 1️⃣ Listar todos los libros
```javascript
// Obtener lista de libros
async function cargarLibros() {
    const response = await fetch('http://tu-servidor.com/api/v1/libros?con_stock=true&per_page=50');
    const data = await response.json();
    
    if (data.success) {
        // Mostrar lista en la UI
        return data.data; // Array de libros
    }
}
```

### 2️⃣ Cuando seleccionen un libro, ver disponibilidad detallada
```javascript
// Usuario selecciona un libro de la lista
async function verDisponibilidadDetallada(libroId) {
    const response = await fetch(`http://tu-servidor.com/api/v1/libros/${libroId}/disponibilidad`);
    const data = await response.json();
    
    if (data.data.tiene_stock) {
        // Mostrar dónde está disponible
        console.log('Inventario General:', data.data.inventario_general.cantidad);
        console.log('Subinventarios:', data.data.subinventarios);
    }
}
```

### 3️⃣ Flujo completo con paginación
```javascript
class CatalogoLibros {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
        this.libros = [];
        this.currentPage = 1;
    }
    
    // Cargar página de libros
    async cargarPagina(page = 1, filtros = {}) {
        const params = new URLSearchParams({
            page: page,
            per_page: 20,
            con_stock: 'true',
            ...filtros
        });
        
        const response = await fetch(`${this.baseUrl}/api/v1/libros?${params}`);
        const data = await response.json();
        
        if (data.success) {
            this.libros = data.data;
            this.pagination = data.pagination;
            return this.libros;
        }
    }
    
    // Buscar libros
    async buscar(termino) {
        return this.cargarPagina(1, { buscar: termino });
    }
    
    // Ver disponibilidad de un libro
    async verDisponibilidad(libroId) {
        const response = await fetch(`${this.baseUrl}/api/v1/libros/${libroId}/disponibilidad`);
        return response.json();
    }
    
    // Cargar siguiente página
    async siguientePagina() {
        if (this.currentPage < this.pagination.last_page) {
            this.currentPage++;
            return this.cargarPagina(this.currentPage);
        }
    }
}

// Uso
const catalogo = new CatalogoLibros('http://tu-servidor.com');

// Cargar primera página
await catalogo.cargarPagina();

// Buscar
await catalogo.buscar('biblia');

// Ver disponibilidad de un libro específico
const disponibilidad = await catalogo.verDisponibilidad(178);
```

---

## 🎯 Casos de Uso

### 1️⃣ Cargar catálogo inicial
```javascript
// En el inicio de tu app
const libros = await fetch('/api/v1/libros?con_stock=true&per_page=50')
    .then(r => r.json())
    .then(d => d.data);

// Mostrar en lista
libros.forEach(libro => {
    console.log(`${libro.nombre} - $${libro.precio} (Stock: ${libro.stock})`);
});
```

### 2️⃣ Implementar búsqueda en tiempo real
```javascript
let timeoutId;
const searchInput = document.getElementById('buscar-libro');

searchInput.addEventListener('input', (e) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(async () => {
        const termino = e.target.value;
        const response = await fetch(`/api/v1/libros?buscar=${termino}&con_stock=true`);
        const data = await response.json();
        
        // Actualizar resultados en UI
        actualizarResultados(data.data);
    }, 300); // Debounce de 300ms
});
```

### 3️⃣ Filtrar por rango de precio
```javascript
async function filtrarPorPrecio(min, max) {
    const response = await fetch(
        `/api/v1/libros?precio_min=${min}&precio_max=${max}&con_stock=true&ordenar=precio&direccion=asc`
    );
    const data = await response.json();
    return data.data;
}

// Ejemplo: Libros entre $50 y $200
const librosRango = await filtrarPorPrecio(50, 200);
```

### 4️⃣ Paginación infinita (scroll infinito)
```javascript
let currentPage = 1;
let isLoading = false;

window.addEventListener('scroll', async () => {
    const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
    
    // Si llegamos al final y no estamos cargando
    if (scrollTop + clientHeight >= scrollHeight - 100 && !isLoading) {
        isLoading = true;
        currentPage++;
        
        const response = await fetch(`/api/v1/libros?page=${currentPage}&per_page=20&con_stock=true`);
        const data = await response.json();
        
        // Agregar libros a la lista existente
        agregarLibrosALista(data.data);
        
        isLoading = false;
    }
});
```

---

## 🔄 Combinando Ambas APIs

```javascript
// Ejemplo completo: Listar y ver disponibilidad
async function mostrarCatalogoConDisponibilidad() {
    // 1. Obtener lista de libros
    const responseLibros = await fetch('/api/v1/libros?con_stock=true&per_page=10');
    const { data: libros } = await responseLibros.json();
    
    // 2. Para cada libro, mostrar info básica
    for (const libro of libros) {
        console.log(`\n📚 ${libro.nombre}`);
        console.log(`   💰 Precio: $${libro.precio}`);
        console.log(`   📦 Stock general: ${libro.stock}`);
        
        // 3. Si el usuario hace clic, obtener disponibilidad detallada
        // (esto lo harías solo cuando el usuario lo solicite)
        const btnVerDetalle = document.createElement('button');
        btnVerDetalle.onclick = async () => {
            const responseDisp = await fetch(`/api/v1/libros/${libro.id}/disponibilidad`);
            const { data: disponibilidad } = await responseDisp.json();
            
            // Mostrar detalle de disponibilidad
            mostrarModalDisponibilidad(disponibilidad);
        };
    }
}
```

---

## 🔐 Notas Importantes

1. **Paginación**: Por defecto retorna 50 libros por página, máximo 100
2. **Performance**: Usa filtros para reducir la cantidad de datos
3. **Caché**: Considera cachear la lista de libros en tu app móvil
4. **Stock**: El campo `stock` es solo del inventario general, usa `/disponibilidad` para ver subinventarios

---

## 🧪 Ejemplos de Prueba

```bash
# Todos los libros (primera página)
curl "http://127.0.0.1:8000/api/v1/libros"

# Solo con stock, 10 por página
curl "http://127.0.0.1:8000/api/v1/libros?con_stock=true&per_page=10"

# Buscar "biblia" con stock
curl "http://127.0.0.1:8000/api/v1/libros?buscar=biblia&con_stock=true"

# Ordenar por precio descendente
curl "http://127.0.0.1:8000/api/v1/libros?ordenar=precio&direccion=desc"

# Página 2 de resultados
curl "http://127.0.0.1:8000/api/v1/libros?page=2&per_page=20"

# Combinación de filtros
curl "http://127.0.0.1:8000/api/v1/libros?con_stock=true&precio_min=50&precio_max=500&ordenar=nombre&per_page=25"
```

---

## ✅ Resumen en 4 Pasos

1. **Lista general**: `GET /api/v1/libros` → Obtén catálogo completo
2. **Filtra**: Usa parámetros `con_stock`, `buscar`, `precio_min/max`
3. **Pagina**: Usa `page` y `per_page` para cargar más resultados
4. **Detalle**: Cuando necesites saber dónde está el stock, usa `GET /api/v1/libros/{id}/disponibilidad`

🎯 **Dos APIs que trabajan juntas:**
- `/libros` = Catálogo general (rápido, lista completa)
- `/libros/{id}/disponibilidad` = Detalle específico (dónde está cada unidad)
