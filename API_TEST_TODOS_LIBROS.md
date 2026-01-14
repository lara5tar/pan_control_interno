# API TESTEO - Listar Todos los Libros con Vendibilidad

## 📋 Resumen

**API de prueba** para la app móvil que muestra **TODOS los libros** del sistema e indica si cada libro puede ser vendido según el **subinventario seleccionado** por el usuario.

---

## 🔗 Endpoint

```
GET /api/v1/test/todos-los-libros
```

---

## 📥 Parámetros Query (todos opcionales)

| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| `cod_congregante` | string | Código del vendedor (para validación) | `14279` |
| `subinventario_id` | integer | **IMPORTANTE:** ID del subinventario seleccionado en la app | `5` |
| `buscar` | string | Buscar por nombre del libro | `biblia` |
| `con_stock` | boolean | Solo libros con stock > 0 en inventario general | `true` |
| `precio_min` | number | Precio mínimo | `50` |
| `precio_max` | number | Precio máximo | `200` |
| `ordenar` | string | Campo: `nombre`, `precio`, `stock`, `created_at` | `precio` |
| `direccion` | string | Dirección: `asc`, `desc` | `desc` |
| `per_page` | integer | Libros por página (1-100, default: 50) | `20` |

---

## 📤 Respuestas

### ✅ Ejemplo 1: Sin subinventario_id (todos los libros básicos)
```bash
curl "http://127.0.0.1:8000/api/v1/test/todos-los-libros?buscar=biblia"
```

**Respuesta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 178,
            "nombre": "BIBLIA THOMPSON",
            "codigo_barras": null,
            "precio": 900,
            "stock": 5,
            "stock_subinventario": 1
        },
        {
            "id": 200,
            "nombre": "BIBLIA INFANTIL",
            "codigo_barras": "9781234567890",
            "precio": 120,
            "stock": 10,
            "stock_subinventario": 0
        }
    ],
    "pagination": {
        "total": 2,
        "per_page": 50,
        "current_page": 1,
        "last_page": 1,
        "from": 1,
        "to": 2
    }
}
```

---

### ✅ Ejemplo 2: CON subinventario_id (indica qué puede vender)
```bash
curl "http://127.0.0.1:8000/api/v1/test/todos-los-libros?subinventario_id=5&buscar=biblia"
```

**Respuesta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 178,
            "nombre": "BIBLIA THOMPSON",
            "codigo_barras": null,
            "precio": 900,
            "stock": 5,
            "stock_subinventario": 1,
            "puede_vender": true,
            "cantidad_disponible_para_mi": 1
        },
        {
            "id": 200,
            "nombre": "BIBLIA INFANTIL",
            "codigo_barras": "9781234567890",
            "precio": 120,
            "stock": 10,
            "stock_subinventario": 0,
            "puede_vender": false,
            "cantidad_disponible_para_mi": 0
        }
    ],
    "resumen": {
        "total_puede_vender": 1,
        "total_no_puede_vender": 1,
        "total_libros_pagina": 2,
        "total_libros_en_subinventario": 26,
        "total_libros_sistema": 183
    },
    "subinventario_actual": {
        "id": 5,
        "descripcion": "Inventario Juan Pérez",
        "estado": "activo"
    },
    "pagination": {
        "total": 2,
        "per_page": 50,
        "current_page": 1,
        "last_page": 1,
        "from": 1,
        "to": 2
    }
}
```

---

### ❌ Error: Subinventario no pertenece al usuario
```bash
curl "http://127.0.0.1:8000/api/v1/test/todos-los-libros?cod_congregante=14279&subinventario_id=999"
```

**Respuesta (403):**
```json
{
    "success": false,
    "message": "El subinventario seleccionado no está asignado a este usuario",
    "error": "unauthorized_subinventario"
}
```

---

## 📊 Estructura de la Respuesta

### Campos Siempre Presentes
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | integer | ID del libro |
| `nombre` | string | Nombre del libro |
| `codigo_barras` | string\|null | Código de barras |
| `precio` | number | Precio del libro |
| `stock` | integer | Stock en inventario general |
| `stock_subinventario` | integer | Total en todos los subinventarios |

### Campos Condicionales (solo si se envía `subinventario_id`)
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `puede_vender` | boolean | ¿Este libro está en el subinventario seleccionado? |
| `cantidad_disponible_para_mi` | integer | Cantidad en el subinventario seleccionado |

### Objeto `resumen` (solo si se envía `subinventario_id`)
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `total_puede_vender` | integer | Libros en esta página que SÍ puede vender |
| `total_no_puede_vender` | integer | Libros en esta página que NO puede vender |
| `total_libros_pagina` | integer | Total de libros en esta página |
| `total_libros_en_subinventario` | integer | Total de libros diferentes en el subinventario (sin filtros) |
| `total_libros_sistema` | integer | Total de libros en todo el sistema (sin filtros) |

### Objeto `subinventario_actual` (solo si se envía `subinventario_id`)
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | integer | ID del subinventario |
| `descripcion` | string | Descripción del subinventario |
| `estado` | string | Estado (activo/completado/cancelado) |

### Objeto `pagination`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `total` | integer | Total de libros en todo el sistema |
| `per_page` | integer | Libros por página |
| `current_page` | integer | Página actual |
| `last_page` | integer | Última página |
| `from` | integer | Número del primer libro en esta página |
| `to` | integer | Número del último libro en esta página |

---

## 💡 Flujo Recomendado en App Móvil

### 1️⃣ Usuario inicia sesión y selecciona subinventario

```javascript
// Paso 1: Login
const usuario = await login(username, password);
const codCongregante = usuario.cod_congregante;

// Paso 2: Obtener subinventarios del usuario
const response = await fetch(
    `http://tu-servidor.com/api/v1/mis-subinventarios/${codCongregante}`
);
const { data: subinventarios } = await response.json();

// Paso 3: Usuario selecciona uno
const subinventarioSeleccionado = subinventarios[0].id;
localStorage.setItem('subinventario_actual', subinventarioSeleccionado);
```

---

### 2️⃣ Listar todos los libros con vendibilidad

```javascript
async function cargarCatalogo(subinventarioId) {
    const response = await fetch(
        `http://tu-servidor.com/api/v1/test/todos-los-libros?subinventario_id=${subinventarioId}&con_stock=true`
    );
    const data = await response.json();
    
    if (!data.success) {
        alert(data.message);
        return;
    }
    
    // Mostrar resumen
    console.log(`📦 Subinventario: ${data.subinventario_actual.descripcion}`);
    console.log(`✅ Puedo vender: ${data.resumen.total_puede_vender} libros (en esta página)`);
    console.log(`❌ No disponibles: ${data.resumen.total_no_puede_vender} libros (en esta página)`);
    console.log(`📚 Total en mi subinventario: ${data.resumen.total_libros_en_subinventario} libros`);
    console.log(`🗂️ Total en sistema: ${data.resumen.total_libros_sistema} libros`);
    
    // Renderizar lista
    data.data.forEach(libro => {
        mostrarLibroEnCatalogo(libro);
    });
}
```

---

### 3️⃣ Mostrar libro con badge de disponibilidad

```javascript
function mostrarLibroEnCatalogo(libro) {
    const card = document.createElement('div');
    card.className = 'libro-card';
    
    // Badge de disponibilidad
    const badge = libro.puede_vender
        ? '<span class="badge badge-success">✓ Disponible para vender</span>'
        : '<span class="badge badge-secondary">✗ No disponible</span>';
    
    // Botón según disponibilidad
    const boton = libro.puede_vender
        ? `<button onclick="agregarAlCarrito(${libro.id})">
             Agregar (${libro.cantidad_disponible_para_mi} disponibles)
           </button>`
        : `<button onclick="verDisponibilidad(${libro.id})" class="btn-outline">
             Ver dónde está disponible
           </button>`;
    
    card.innerHTML = `
        <h3>${libro.nombre}</h3>
        <p class="precio">$${libro.precio}</p>
        ${badge}
        <p class="stock-info">
            Stock sistema: ${libro.stock}
            ${libro.puede_vender ? `<br>Mi stock: ${libro.cantidad_disponible_para_mi}` : ''}
        </p>
        ${boton}
    `;
    
    document.getElementById('catalogo').appendChild(card);
}
```

---

### 4️⃣ Validar antes de agregar al carrito

```javascript
async function agregarAlCarrito(libroId, cantidad = 1) {
    const subinventarioId = localStorage.getItem('subinventario_actual');
    
    // Consultar info del libro
    const response = await fetch(
        `http://tu-servidor.com/api/v1/test/todos-los-libros?subinventario_id=${subinventarioId}`
    );
    const { data } = await response.json();
    
    const libro = data.find(l => l.id === libroId);
    
    if (!libro) {
        alert('Libro no encontrado');
        return false;
    }
    
    if (!libro.puede_vender) {
        alert('No puedes vender este libro desde tu subinventario actual.');
        verDisponibilidad(libroId);
        return false;
    }
    
    if (libro.cantidad_disponible_para_mi < cantidad) {
        alert(`Solo tienes ${libro.cantidad_disponible_para_mi} unidades disponibles`);
        return false;
    }
    
    // OK - agregar al carrito
    carrito.agregar(libro, cantidad);
    actualizarVistaCarrito();
    return true;
}
```

---

### 5️⃣ Buscar libros en tiempo real

```javascript
let timeoutBusqueda;

function buscarLibros(termino) {
    // Debounce para no hacer muchas peticiones
    clearTimeout(timeoutBusqueda);
    
    timeoutBusqueda = setTimeout(async () => {
        const subinventarioId = localStorage.getItem('subinventario_actual');
        
        const response = await fetch(
            `http://tu-servidor.com/api/v1/test/todos-los-libros?` +
            `subinventario_id=${subinventarioId}&` +
            `buscar=${encodeURIComponent(termino)}&` +
            `con_stock=true`
        );
        
        const { data, resumen } = await response.json();
        
        // Actualizar resultados
        actualizarResultados(data, resumen);
    }, 500);
}

// HTML del buscador
<input 
    type="search" 
    placeholder="Buscar libros..." 
    oninput="buscarLibros(this.value)"
/>
```

---

### 6️⃣ Cambiar de subinventario

```javascript
async function cambiarSubinventario(nuevoSubinventarioId) {
    // Validar que el carrito esté vacío
    if (carrito.length > 0) {
        const confirmar = confirm(
            'Al cambiar de subinventario se vaciará tu carrito. ¿Continuar?'
        );
        if (!confirmar) return;
        carrito.vaciar();
    }
    
    // Actualizar subinventario actual
    localStorage.setItem('subinventario_actual', nuevoSubinventarioId);
    
    // Recargar catálogo
    await cargarCatalogo(nuevoSubinventarioId);
    
    alert('Subinventario cambiado exitosamente');
}
```

---

## 🧪 Ejemplos de Prueba

### 1. Ver todos los libros (sin vendibilidad)
```bash
curl "http://127.0.0.1:8000/api/v1/test/todos-los-libros"
```

### 2. Ver todos con vendibilidad (subinventario 5)
```bash
curl "http://127.0.0.1:8000/api/v1/test/todos-los-libros?subinventario_id=5"
```

### 3. Buscar "biblia" en mi subinventario
```bash
curl "http://127.0.0.1:8000/api/v1/test/todos-los-libros?subinventario_id=5&buscar=biblia"
```

### 4. Solo libros con stock que pueda vender
```bash
curl "http://127.0.0.1:8000/api/v1/test/todos-los-libros?subinventario_id=5&con_stock=true"
```

### 5. Filtrar por precio
```bash
curl "http://127.0.0.1:8000/api/v1/test/todos-los-libros?subinventario_id=5&precio_min=50&precio_max=500"
```

### 6. Ordenar por precio (menor a mayor)
```bash
curl "http://127.0.0.1:8000/api/v1/test/todos-los-libros?subinventario_id=5&ordenar=precio&direccion=asc"
```

### 7. Con validación de usuario
```bash
curl "http://127.0.0.1:8000/api/v1/test/todos-los-libros?cod_congregante=14279&subinventario_id=5"
```

### 8. Primera página de 20 libros
```bash
curl "http://127.0.0.1:8000/api/v1/test/todos-los-libros?subinventario_id=5&per_page=20"
```

---

## 🎯 Casos de Uso

### Caso 1: Catálogo general con filtro visual
```javascript
class CatalogoApp {
    constructor() {
        this.subinventarioActual = null;
    }
    
    async inicializar(codCongregante) {
        // Cargar subinventarios del usuario
        const subs = await this.obtenerSubinventarios(codCongregante);
        
        // Seleccionar el primero por defecto
        this.subinventarioActual = subs[0].id;
        
        // Cargar catálogo
        await this.cargarCatalogo();
    }
    
    async cargarCatalogo(filtros = {}) {
        const params = new URLSearchParams({
            subinventario_id: this.subinventarioActual,
            con_stock: true,
            ...filtros
        });
        
        const response = await fetch(`/api/v1/test/todos-los-libros?${params}`);
        const { data, resumen, subinventario_actual } = await response.json();
        
        // Mostrar info del subinventario actual
        this.mostrarInfoSubinventario(subinventario_actual);
        
        // Separar en dos listas
        const disponibles = data.filter(l => l.puede_vender);
        const noDisponibles = data.filter(l => !l.puede_vender);
        
        // Renderizar
        this.renderizarLista('disponibles', disponibles);
        this.renderizarLista('no-disponibles', noDisponibles);
        
        // Mostrar estadísticas
        this.mostrarEstadisticas(resumen);
    }
}
```

### Caso 2: Mostrar estadísticas del subinventario
```javascript
function mostrarEstadisticas(resumen, subinventarioActual) {
    const estadisticas = document.getElementById('estadisticas');
    
    estadisticas.innerHTML = `
        <div class="stats-card">
            <h4>📦 ${subinventarioActual.descripcion || 'Mi Subinventario'}</h4>
            
            <div class="stat-group">
                <h5>RESUMEN GENERAL</h5>
                <p>🗂️ Total en sistema: <strong>${resumen.total_libros_sistema}</strong> libros</p>
                <p>📦 En mi subinventario: <strong>${resumen.total_libros_en_subinventario}</strong> libros</p>
                <p class="porcentaje">
                    Tienes el ${((resumen.total_libros_en_subinventario / resumen.total_libros_sistema) * 100).toFixed(1)}% 
                    del catálogo
                </p>
            </div>
            
            <div class="stat-group">
                <h5>EN ESTA PÁGINA</h5>
                <p>✅ Disponibles: <strong>${resumen.total_puede_vender}</strong></p>
                <p>❌ No disponibles: <strong>${resumen.total_no_puede_vender}</strong></p>
            </div>
        </div>
    `;
}

// Ejemplo de uso:
const { data, resumen, subinventario_actual } = await response.json();
mostrarEstadisticas(resumen, subinventario_actual);
```

### Caso 3: Ver disponibilidad en otros lugares
```javascript
async function verDisponibilidad(libroId) {
    // Usar la otra API para ver en qué subinventarios SÍ está
    const response = await fetch(
        `http://tu-servidor.com/api/v1/libros/${libroId}/disponibilidad`
    );
    const data = await response.json();
    
    mostrarModal({
        titulo: data.libro.nombre,
        contenido: `
            <h4>Este libro NO está en tu subinventario actual</h4>
            <p>Pero SÍ hay stock disponible en:</p>
            
            ${data.inventario_general.disponible ? `
                <div class="ubicacion">
                    📦 Inventario General: ${data.inventario_general.cantidad} unidades
                </div>
            ` : ''}
            
            ${data.subinventarios.map(sub => `
                <div class="ubicacion">
                    📦 ${sub.nombre}: ${sub.cantidad} unidades
                </div>
            `).join('')}
            
            <p class="nota">
                💡 Puedes pedirlo al administrador o cambiar de subinventario
                si tienes acceso a alguno de los anteriores.
            </p>
        `
    });
}
```

---

## 🔒 Validaciones

### 1. Validación de acceso al subinventario
Si envías `cod_congregante` + `subinventario_id`, la API valida que el subinventario le pertenezca al usuario. Si no, retorna error 403.

### 2. Paginación
- `per_page` máximo: 100
- Default: 50 libros por página

### 3. Ordenamiento
Campos permitidos: `nombre`, `precio`, `stock`, `created_at`

### 4. Filtros de precio
Ambos deben ser números mayores o iguales a 0.

---

## 📝 Notas Importantes

1. **Esta API es de TESTEO**: Está en la ruta `/api/v1/test/` para indicar que es experimental.

2. **Parámetro `subinventario_id` es clave**: Sin él, solo ves info básica de todos los libros. Con él, sabes qué puedes vender desde ese subinventario.

3. **Validación opcional de usuario**: Si envías `cod_congregante` junto con `subinventario_id`, se valida que el subinventario le pertenezca.

4. **Muestra TODOS los libros**: No filtra por disponibilidad, muestra el catálogo completo con un indicador de vendibilidad.

5. **Compatible con paginación**: Ideal para catálogos grandes.

6. **Resumen por página**: El campo `resumen` muestra estadísticas de la página actual, NO del total.

---

## 🔗 APIs Relacionadas

### 1. Mis Subinventarios
```
GET /api/v1/mis-subinventarios/{cod_congregante}
```
Lista los subinventarios asignados al usuario (para que seleccione uno).

### 2. Ver Disponibilidad Detallada
```
GET /api/v1/libros/{id}/disponibilidad
```
Muestra en qué subinventarios e inventario general está un libro específico.

### 3. Mis Libros Disponibles
```
GET /api/v1/mis-libros-disponibles/{cod_congregante}
```
Retorna SOLO los libros que puede vender (de TODOS sus subinventarios).

---

## ✅ Diferencias con Otras APIs

| Característica | `/test/todos-los-libros` | `/mis-libros-disponibles/{cod}` | `/libros` |
|----------------|-------------------------|--------------------------------|-----------|
| **Qué muestra** | TODOS los libros | SOLO vendibles | TODOS los libros |
| **Requiere subinventario** | Sí (para vendibilidad) | No (usa todos) | No |
| **Campo `puede_vender`** | Por subinventario seleccionado | Siempre true | Según cod_congregante |
| **Uso principal** | Catálogo con subinv. específico | Catálogo del vendedor | Búsqueda general |
| **Ruta** | `/api/v1/test/` | `/api/v1/` | `/api/v1/` |

---

## 🚀 Flujo Completo en App Móvil

```javascript
// 1. Login
const user = await login();

// 2. Cargar subinventarios del usuario
const { data: subinventarios } = await fetch(`/api/v1/mis-subinventarios/${user.cod_congregante}`);

// 3. Usuario selecciona uno
const subinvSeleccionado = subinventarios[0].id;

// 4. Cargar catálogo completo con vendibilidad
const catalogo = await fetch(
    `/api/v1/test/todos-los-libros?subinventario_id=${subinvSeleccionado}&con_stock=true`
);

// 5. Mostrar con badges y filtros
mostrarCatalogo(catalogo.data);

// 6. Usuario busca
onSearch(async (termino) => {
    const resultados = await fetch(
        `/api/v1/test/todos-los-libros?` +
        `subinventario_id=${subinvSeleccionado}&buscar=${termino}`
    );
    actualizarResultados(resultados.data);
});

// 7. Agregar al carrito (solo si puede_vender: true)
onAgregarCarrito(async (libroId) => {
    const libro = await buscarLibro(libroId);
    if (libro.puede_vender) {
        carrito.agregar(libro);
    } else {
        alert('No disponible en tu subinventario');
        verDisponibilidad(libroId);
    }
});
```

---

🎯 **Esta API te permite mostrar TODO el catálogo e indicar visualmente qué puede vender el usuario según su subinventario seleccionado!**
