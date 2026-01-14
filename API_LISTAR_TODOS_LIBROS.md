# API - Listar Todos los Libros

## 📋 Resumen Rápido

**API PARA BÚSQUEDA GENERAL:** Esta API muestra TODOS los libros del sistema. Si proporcionas el `cod_congregante`, te dice qué libros puedes vender y cuáles no.

---

## 🔗 Endpoint

```
GET /api/v1/libros
```

**Parámetros Query (todos opcionales):**

| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| `cod_congregante` | string | **IMPORTANTE:** Código del vendedor para saber qué puede vender | `cod_congregante=14279` |
| `buscar` | string | Buscar por nombre del libro | `buscar=biblia` |
| `con_stock` | boolean | Solo libros con stock > 0 | `con_stock=true` |
| `precio_min` | number | Precio mínimo | `precio_min=50` |
| `precio_max` | number | Precio máximo | `precio_max=200` |
| `ordenar` | string | Campo: `nombre`, `precio`, `stock`, `created_at` | `ordenar=precio` |
| `direccion` | string | Dirección: `asc`, `desc` | `direccion=desc` |
| `per_page` | integer | Libros por página (máx 100) | `per_page=20` |

---

## 📤 Respuesta

### ✅ Ejemplo 1: Sin `cod_congregante` (todos los libros sin info de vendibilidad)
```bash
curl "http://127.0.0.1:8000/api/v1/libros?buscar=biblia"
```

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
            "stock_apartado": 0
        }
    ],
    "pagination": {
        "total": 1,
        "per_page": 50,
        "current_page": 1,
        "last_page": 1,
        "from": 1,
        "to": 1
    }
}
```

### ✅ Ejemplo 2: CON `cod_congregante` (indica qué puede vender)
```bash
curl "http://127.0.0.1:8000/api/v1/libros?cod_congregante=14279&buscar=biblia"
```

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
            "stock_apartado": 0,
            "puede_vender": true,
            "cantidad_disponible_para_mi": 1
        },
        {
            "id": 200,
            "nombre": "BIBLIA INFANTIL",
            "codigo_barras": null,
            "precio": 120,
            "stock": 10,
            "stock_subinventario": 0,
            "stock_apartado": 0,
            "puede_vender": false,
            "cantidad_disponible_para_mi": 0
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

### 🔍 Ejemplo 3: Vendedor sin subinventarios
```bash
curl "http://127.0.0.1:8000/api/v1/libros?cod_congregante=99999"
```

```json
{
    "success": true,
    "data": [
        {
            "id": 178,
            "nombre": "BIBLIA THOMPSON",
            "precio": 900,
            "stock": 5,
            "puede_vender": false,
            "cantidad_disponible_para_mi": 0
        }
    ]
}
```
> **Nota:** Todos los libros aparecen con `puede_vender: false` porque este vendedor no tiene subinventarios asignados.

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
| `stock_apartado` | integer | Total en apartados |

### Campos Condicionales (solo si se envía `cod_congregante`)
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `puede_vender` | boolean | ¿Este vendedor puede vender este libro? |
| `cantidad_disponible_para_mi` | integer | Cantidad en MIS subinventarios |

---

## 💡 Flujo en App Móvil

### 1️⃣ Búsqueda General de Libros
```javascript
// Usuario busca cualquier libro, sepa o no si puede venderlo
async function buscarLibros(termino, codCongregante) {
    const response = await fetch(
        `http://tu-servidor.com/api/v1/libros?buscar=${termino}&cod_congregante=${codCongregante}`
    );
    const data = await response.json();
    
    return data.data.map(libro => ({
        ...libro,
        // Indica visualmente si puede venderlo
        badgeColor: libro.puede_vender ? 'green' : 'gray',
        badgeText: libro.puede_vender ? 'Disponible' : 'No disponible'
    }));
}
```

### 2️⃣ Mostrar Libro con Badge
```javascript
function mostrarLibro(libro) {
    return `
        <div class="libro-card">
            <h3>${libro.nombre}</h3>
            <p class="precio">$${libro.precio}</p>
            
            ${libro.puede_vender !== undefined ? `
                <span class="badge ${libro.puede_vender ? 'badge-success' : 'badge-secondary'}">
                    ${libro.puede_vender ? '✓ Puedo vender' : '✗ No disponible'}
                </span>
            ` : ''}
            
            <p class="stock">
                Stock total sistema: ${libro.stock}
                ${libro.puede_vender !== undefined ? `
                    <br>Mi stock: ${libro.cantidad_disponible_para_mi}
                ` : ''}
            </p>
            
            ${libro.puede_vender ? `
                <button onclick="agregarAlCarrito(${libro.id})">
                    Agregar al Carrito
                </button>
            ` : `
                <button onclick="verDisponibilidad(${libro.id})">
                    Ver dónde está disponible
                </button>
            `}
        </div>
    `;
}
```

### 3️⃣ Ver Disponibilidad en Otros Inventarios
```javascript
async function verDisponibilidad(libroId) {
    // Usar la otra API para ver dónde SÍ hay stock
    const response = await fetch(
        `http://tu-servidor.com/api/v1/libros/${libroId}/disponibilidad`
    );
    const data = await response.json();
    
    if (data.tiene_stock) {
        mostrarModal(`
            <h3>${data.libro.nombre}</h3>
            <p>Este libro SÍ tiene stock, pero en otros inventarios:</p>
            
            ${data.inventario_general.disponible ? `
                <div class="stock-item">
                    📦 Inventario General: ${data.inventario_general.cantidad} unidades
                </div>
            ` : ''}
            
            ${data.subinventarios.map(sub => `
                <div class="stock-item">
                    📦 ${sub.nombre}: ${sub.cantidad} unidades
                </div>
            `).join('')}
            
            <p class="nota">No puedes venderlo desde la app, pero puedes pedirlo al administrador.</p>
        `);
    }
}
```

### 4️⃣ Validar Antes de Agregar al Carrito
```javascript
async function agregarAlCarrito(libroId, cantidad) {
    const codCongregante = obtenerCodUsuarioLogueado();
    
    // Buscar el libro con info de vendibilidad
    const response = await fetch(
        `http://tu-servidor.com/api/v1/libros?cod_congregante=${codCongregante}`
    );
    const { data } = await response.json();
    
    const libro = data.find(l => l.id === libroId);
    
    if (!libro) {
        alert("Libro no encontrado");
        return false;
    }
    
    if (!libro.puede_vender) {
        alert("No puedes vender este libro. No está en tu inventario asignado.");
        // Mostrar disponibilidad en otros inventarios
        verDisponibilidad(libroId);
        return false;
    }
    
    if (libro.cantidad_disponible_para_mi < cantidad) {
        alert(`Solo tienes ${libro.cantidad_disponible_para_mi} unidades disponibles`);
        return false;
    }
    
    // OK - agregar
    carrito.agregar(libro, cantidad);
    return true;
}
```

---

## 🎯 Casos de Uso

### 1️⃣ Catálogo General con Indicador de Vendibilidad
```javascript
class AppVentas {
    constructor() {
        this.codCongregante = null;
    }
    
    async buscarEnCatalogo(termino) {
        const url = `/api/v1/libros?buscar=${termino}&cod_congregante=${this.codCongregante}`;
        const response = await fetch(url);
        const { data } = await response.json();
        
        return data.map(libro => ({
            ...libro,
            // Agregar info visual
            disponibilidadTexto: libro.puede_vender 
                ? `✓ Tengo ${libro.cantidad_disponible_para_mi} disponibles`
                : '✗ No disponible en mi inventario',
            puedeAgregar: libro.puede_vender && libro.cantidad_disponible_para_mi > 0
        }));
    }
}
```

### 2️⃣ Mostrar TODOS los Libros con Filtro Visual
```javascript
async function cargarCatalogoCompleto() {
    const codCongregante = obtenerCodUsuarioLogueado();
    const response = await fetch(`/api/v1/libros?cod_congregante=${codCongregante}&per_page=100`);
    const { data, pagination } = await response.json();
    
    // Separar por disponibilidad
    const misLibros = data.filter(l => l.puede_vender);
    const otrosLibros = data.filter(l => !l.puede_vender);
    
    console.log(`Puedo vender: ${misLibros.length}`);
    console.log(`No puedo vender: ${otrosLibros.length}`);
    
    // Mostrar en tabs separados
    mostrarTab('disponibles', misLibros);
    mostrarTab('no-disponibles', otrosLibros);
}
```

### 3️⃣ Búsqueda Universal + Ver Disponibilidad
```javascript
async function buscarYMostrarDisponibilidad(termino) {
    const codCongregante = obtenerCodUsuarioLogueado();
    
    // 1. Buscar libro
    const response = await fetch(
        `/api/v1/libros?buscar=${termino}&cod_congregante=${codCongregante}`
    );
    const { data } = await response.json();
    
    data.forEach(libro => {
        const card = crearTarjetaLibro(libro);
        
        // Si NO puede venderlo, agregar botón para ver dónde SÍ hay
        if (!libro.puede_vender && libro.stock > 0) {
            card.appendChild(crearBoton(
                'Ver dónde está disponible',
                () => verDisponibilidadDetallada(libro.id)
            ));
        }
    });
}

async function verDisponibilidadDetallada(libroId) {
    // 2. Usar la otra API para ver ubicaciones
    const response = await fetch(`/api/v1/libros/${libroId}/disponibilidad`);
    const data = await response.json();
    
    mostrarModal({
        titulo: data.libro.nombre,
        contenido: `
            <h4>Disponibilidad en el sistema:</h4>
            ${data.inventario_general.disponible ? `
                <p>✓ Inventario General: ${data.inventario_general.cantidad}</p>
            ` : ''}
            ${data.subinventarios.map(sub => `
                <p>✓ ${sub.nombre}: ${sub.cantidad} unidades</p>
            `).join('')}
            <p class="nota">Contacta al administrador para solicitar este libro.</p>
        `
    });
}
```

---

## 🔐 Notas Importantes

1. **Parámetro `cod_congregante` es clave**: Sin él, solo ves info básica. Con él, sabes qué puedes vender.
2. **Muestra TODOS los libros**: A diferencia de `/mis-libros-disponibles`, esta API no filtra.
3. **Ideal para búsqueda**: El usuario busca cualquier libro y ve si puede venderlo o no.
4. **Combinar con API de disponibilidad**: Si `puede_vender: false`, usa `/libros/{id}/disponibilidad` para ver dónde SÍ hay stock.
5. **Paginación**: Soporta páginas para catálogos grandes.

---

## 🧪 Ejemplos de Prueba

```bash
# Ver todos los libros (sin info de vendibilidad)
curl "http://127.0.0.1:8000/api/v1/libros"

# Ver todos los libros CON info de vendibilidad
curl "http://127.0.0.1:8000/api/v1/libros?cod_congregante=14279"

# Buscar "biblia" y saber si puedo venderla
curl "http://127.0.0.1:8000/api/v1/libros?buscar=biblia&cod_congregante=14279"

# Solo libros con stock, que yo pueda vender
curl "http://127.0.0.1:8000/api/v1/libros?con_stock=true&cod_congregante=14279"

# Filtrar por precio y ordenar
curl "http://127.0.0.1:8000/api/v1/libros?precio_min=50&precio_max=500&ordenar=precio&direccion=asc&cod_congregante=14279"

# Primera página de 20 libros
curl "http://127.0.0.1:8000/api/v1/libros?per_page=20&cod_congregante=14279"

# Vendedor sin subinventarios (todos con puede_vender: false)
curl "http://127.0.0.1:8000/api/v1/libros?cod_congregante=99999"
```

---

## ✅ Diferencias entre las APIs

| Característica | `/api/v1/libros` | `/api/v1/mis-libros-disponibles/{cod}` |
|----------------|------------------|----------------------------------------|
| **Qué muestra** | TODOS los libros | SOLO los que puedo vender |
| **Filtro automático** | No | Sí (por subinventarios) |
| **Campo `puede_vender`** | Solo si envías `cod_congregante` | Siempre `true` |
| **Paginación** | Sí | No |
| **Uso principal** | Búsqueda general | Catálogo del vendedor |
| **Info de subinventarios** | No | Sí (detalle de ubicaciones) |

---

## 🔗 APIs Relacionadas

### 1. **Ver Disponibilidad Detallada**
```
GET /api/v1/libros/{id}/disponibilidad
```
Úsala cuando `puede_vender: false` pero `stock > 0` para ver dónde SÍ hay el libro.

### 2. **Mis Libros Disponibles** (API alternativa)
```
GET /api/v1/mis-libros-disponibles/{cod_congregante}
```
Retorna SOLO los libros que el vendedor puede vender (con más detalles).

### 3. **Mis Subinventarios**
```
GET /api/v1/mis-subinventarios/{cod_congregante}
```
Lista los subinventarios asignados al vendedor.

---

## 🎯 Flujo Recomendado para App Móvil

```javascript
// 1. Login
const usuario = await login(username, password);
const codCongregante = usuario.cod_congregante;

// 2. Búsqueda general (muestra TODOS los libros)
async function buscar(termino) {
    const response = await fetch(
        `/api/v1/libros?buscar=${termino}&cod_congregante=${codCongregante}&con_stock=true`
    );
    const { data } = await response.json();
    
    // Mostrar cada libro con su badge
    data.forEach(libro => {
        mostrarLibro(libro); // Ver función en ejemplos arriba
    });
}

// 3. Si el usuario toca un libro que NO puede vender
async function verMasInfo(libroId) {
    const response = await fetch(`/api/v1/libros/${libroId}/disponibilidad`);
    const info = await response.json();
    
    mostrarModal(`
        Este libro no está en tu inventario, pero SÍ hay stock en:
        ${info.subinventarios.map(s => `- ${s.nombre}: ${s.cantidad}`).join('\n')}
        
        Puedes pedirlo al administrador.
    `);
}

// 4. Si puede vender - agregar al carrito
function agregarAlCarrito(libro) {
    if (libro.puede_vender) {
        carrito.push(libro);
    }
}
```

🎯 **Usa esta API para la búsqueda principal de tu app móvil!**
