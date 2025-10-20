# 📚 Control Interno - Sistema de Inventario<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>



Sistema de gestión de inventario desarrollado con **Laravel 12**, **Tailwind CSS 4** y **JavaScript Puro (Vanilla JS)**.<p align="center">

<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>

## 🎯 Stack Tecnológico<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>

<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>

### ✅ Lo que SÍ usamos<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>

- **Backend**: Laravel 12</p>

- **Estilos**: Tailwind CSS 4

- **JavaScript**: Vanilla JS (ES6+) puro## About Laravel

- **Templates**: Blade

- **Base de datos**: MySQL 8Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- **Build Tool**: Vite

- [Simple, fast routing engine](https://laravel.com/docs/routing).

### ❌ Lo que NO usamos- [Powerful dependency injection container](https://laravel.com/docs/container).

- ❌ **Livewire** - Solo JavaScript puro- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.

- ❌ **Alpine.js** - Solo JavaScript puro  - Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).

- ❌ **jQuery** - Solo JavaScript puro- Database agnostic [schema migrations](https://laravel.com/docs/migrations).

- ❌ **Vue.js / React** - Solo JavaScript puro- [Robust background job processing](https://laravel.com/docs/queues).

- ❌ **Inertia.js** - Solo Blade- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).



## 🚀 Inicio RápidoLaravel is accessible, powerful, and provides tools required for large, robust applications.



```bash## Learning Laravel

# Ya estás en el proyecto

cd /Users/usuario/Desktop/pan_control_internoLaravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.



# Los servidores ya están corriendo:You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

# - Laravel: http://127.0.0.1:8002

# - Vite: http://localhost:5173If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

```

## Laravel Sponsors

### Ver Ejemplos Interactivos

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

Visita: **http://127.0.0.1:8002/ejemplos-js** para ver todos los componentes JavaScript en acción.

### Premium Partners

## 📁 Estructura del Proyecto

- **[Vehikl](https://vehikl.com)**

```- **[Tighten Co.](https://tighten.co)**

pan_control_interno/- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**

├── resources/- **[64 Robots](https://64robots.com)**

│   ├── js/- **[Curotec](https://www.curotec.com/services/technologies/laravel)**

│   │   └── app.js              ← JavaScript PURO aquí- **[DevSquad](https://devsquad.com/hire-laravel-developers)**

│   ├── css/- **[Redberry](https://redberry.international/laravel-development)**

│   │   └── app.css             ← Tailwind CSS aquí- **[Active Logic](https://activelogic.com)**

│   └── views/

│       ├── components/         ← Componentes reutilizables## Contributing

│       │   ├── sidebar.blade.php

│       │   ├── button.blade.phpThank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

│       │   ├── modal.blade.php

│       │   ├── loading.blade.php## Code of Conduct

│       │   └── confirm-dialog.blade.php

│       └── inventario/         ← Módulo de librosIn order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

├── COLORES.md                  ← Cómo cambiar colores

├── TAILWIND.md                 ← Guía de Tailwind## Security Vulnerabilities

└── JAVASCRIPT.md               ← Guía de JavaScript

```If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.



## 🎨 Sistema de Colores (Azul Claro)## License



**Archivo**: `resources/views/layouts/app.blade.php`The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


```javascript
primary: {
    500: '#0ea5e9',  // Color principal
    600: '#0284c7',  // Hover
    700: '#0369a1',  // Active
}
```

**Para cambiar colores**: Edita solo este archivo y todos los componentes se actualizan automáticamente.

👉 Ver [COLORES.md](COLORES.md) para guía completa con paletas predefinidas.

## 💻 JavaScript Puro - Ejemplos

### Notificaciones
```javascript
showNotification('Mensaje guardado', 'success');
showNotification('Error al procesar', 'error');
```

### Confirmación
```javascript
confirmDialog.show('¿Eliminar registro?', (confirmed) => {
    if (confirmed) {
        // Realizar acción
    }
});
```

### Loading Spinner
```javascript
loadingSpinner.show('Procesando...');
// ... hacer algo
loadingSpinner.hide();
```

### Modal
```html
<button onclick="openModal('miModal')">Abrir</button>

<x-modal id="miModal" title="Título">
    Contenido del modal
</x-modal>
```

👉 Ver [JAVASCRIPT.md](JAVASCRIPT.md) para guía completa con todos los ejemplos.

## 🧩 Componentes Blade

### Uso de Componentes

```blade
<!-- Botones -->
<x-button variant="primary" icon="fas fa-plus">Agregar</x-button>
<x-button variant="danger" icon="fas fa-trash">Eliminar</x-button>

<!-- Cards -->
<x-card title="Título">
    Contenido de la tarjeta
</x-card>

<!-- Alertas -->
<x-alert type="success" message="Operación exitosa" />
<x-alert type="error" message="Ha ocurrido un error" />

<!-- Modal -->
<x-modal id="uniqueId" title="Mi Modal">
    Contenido
</x-modal>
```

## 📊 Módulo de Inventario

### Funcionalidades
- ✅ CRUD completo de libros
- ✅ Búsqueda por nombre/código
- ✅ Validación en tiempo real (JavaScript puro)
- ✅ Confirmaciones de eliminación
- ✅ Indicadores visuales de stock
- ✅ Paginación

### Modelo de Datos (Libro)
```php
- id (integer)
- nombre (string)
- codigo_barras (string, único)
- precio (double)
- stock (integer)
- timestamps
```

## 🛠️ Comandos Útiles

```bash
# Ver el sitio
open http://127.0.0.1:8002

# Ver ejemplos de JavaScript
open http://127.0.0.1:8002/ejemplos-js

# Si necesitas reiniciar servidores:
npm run dev              # Terminal 1
php artisan serve        # Terminal 2
```

## 📚 Documentación Completa

1. **[COLORES.md](COLORES.md)** - Guía de personalización de colores
   - Cómo cambiar el color del sistema
   - Paletas predefinidas (Verde, Púrpura, Rojo, etc.)
   - Herramientas para generar colores

2. **[TAILWIND.md](TAILWIND.md)** - Arquitectura de Tailwind
   - Estructura de archivos
   - Patrones de diseño
   - Clases útiles
   - Mejores prácticas

3. **[JAVASCRIPT.md](JAVASCRIPT.md)** - Guía de JavaScript Puro
   - Todas las utilidades disponibles
   - Ejemplos de código
   - Patrones recomendados
   - Guía de migración desde jQuery

## ✨ Características Clave

### 🎯 JavaScript Puro
- ✅ Sin frameworks ni librerías adicionales
- ✅ Validación en tiempo real
- ✅ Notificaciones toast
- ✅ Modales y dialogs
- ✅ Confirmaciones
- ✅ Loading spinners
- ✅ Utilidades de tabla (ordenar, filtrar)
- ✅ Tooltips
- ✅ Copiar al portapapeles

### 🎨 Tailwind CSS
- ✅ Colores personalizados centralizados
- ✅ Componentes reutilizables
- ✅ Responsive design
- ✅ Hot reload con Vite
- ✅ Sin CSS personalizado innecesario

### 🔧 Laravel
- ✅ Blade templates
- ✅ Componentes reutilizables
- ✅ Validación backend
- ✅ Eloquent ORM
- ✅ CSRF protection
- ✅ Rutas resourceful

## 🎓 Para Empezar a Desarrollar

### 1. Agregar Nueva Funcionalidad JavaScript

Edita `resources/js/app.js`:

```javascript
function miFuncion() {
    // Tu código aquí
}

// Hacer global
window.miFuncion = miFuncion;
```

### 2. Crear Nuevo Componente Blade

```bash
touch resources/views/components/mi-componente.blade.php
```

Úsalo en vistas:
```blade
<x-mi-componente />
```

### 3. Cambiar Color del Sistema

Edita `resources/views/layouts/app.blade.php` línea ~15:

```javascript
primary: {
    500: '#TU_COLOR_AQUI',
}
```

## 🎯 Filosofía del Proyecto

Este proyecto está diseñado para ser:

1. **Simple** - Solo las tecnologías necesarias
2. **Mantenible** - Código limpio y organizado
3. **Escalable** - Fácil de extender
4. **Moderno** - Usando estándares web actuales
5. **Sin dependencias innecesarias** - JavaScript puro, sin frameworks

## 📌 Estado Actual

✅ **Completamente funcional**
- Sistema de inventario de libros CRUD
- Dashboard con estadísticas
- JavaScript puro implementado
- Tailwind CSS configurado
- Colores personalizados (azul claro)
- Componentes reutilizables
- Validaciones
- Notificaciones
- Modales
- Loading spinners

## 🚀 URLs Importantes

- **App Principal**: http://127.0.0.1:8002
- **Dashboard**: http://127.0.0.1:8002/
- **Inventario**: http://127.0.0.1:8002/inventario
- **Ejemplos JS**: http://127.0.0.1:8002/ejemplos-js

---

**💡 Tip**: Empieza visitando `/ejemplos-js` para ver todos los componentes JavaScript en acción.
