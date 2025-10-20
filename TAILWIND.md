# Arquitectura Tailwind CSS - Control Interno

## 🎯 Sistema de Diseño con Tailwind

Este proyecto usa **Tailwind CSS** como framework principal de estilos, con una configuración personalizada para colores del sistema.

## 📁 Estructura de Archivos

```
pan_control_interno/
├── resources/
│   ├── css/
│   │   └── app.css                 # Directivas Tailwind + estilos custom
│   ├── views/
│   │   ├── layouts/
│   │   │   └── app.blade.php       # Layout principal con config Tailwind
│   │   └── components/             # Componentes reutilizables
│       └── *.blade.php
├── tailwind.config.js              # Configuración de Tailwind (opcional)
├── postcss.config.js               # Configuración de PostCSS
└── vite.config.js                  # Configuración de Vite
```

## 🎨 Sistema de Colores Personalizado

### Definición de Colores

Los colores están definidos en `app.blade.php` usando la API de configuración de Tailwind CDN:

```javascript
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: { /* azul claro */ },
                accent: { /* amarillo */ }
            }
        }
    }
}
```

### Uso en Componentes

```html
<!-- Fondo -->
<div class="bg-primary-500">...</div>

<!-- Texto -->
<span class="text-primary-600">...</span>

<!-- Hover -->
<button class="bg-primary-500 hover:bg-primary-600">...</button>

<!-- Focus -->
<input class="focus:ring-primary-500 focus:border-primary-500">

<!-- Gradiente -->
<aside class="bg-gradient-to-b from-primary-500 to-primary-600">...</aside>
```

## 🧩 Componentes Blade Reutilizables

### Sidebar (`components/sidebar.blade.php`)
```html
<aside class="w-64 bg-gradient-to-b from-primary-500 to-primary-600 ...">
    <!-- Contenido del sidebar -->
</aside>
```

### Menu Item (`components/menu-item.blade.php`)
```php
@props(['icon', 'label', 'route', 'active' => false])

<a class="flex items-center px-6 py-3 text-white 
    hover:bg-primary-600 
    {{ $active ? 'bg-primary-700 border-l-4 border-accent-500' : '' }}">
    <!-- Contenido del item -->
</a>
```

### Button (`components/button.blade.php`)
```php
@props(['variant' => 'primary'])

@php
    $classes = [
        'primary' => 'bg-primary-500 hover:bg-primary-600 text-white',
        // ... otras variantes
    ];
@endphp

<button class="px-4 py-2 rounded-lg {{ $classes[$variant] }}">
    {{ $slot }}
</button>
```

## 📊 Patrones de Diseño

### Estados Interactivos

```html
<!-- Normal → Hover → Active -->
<button class="
    bg-primary-500 
    hover:bg-primary-600 
    active:bg-primary-700
    transition-colors duration-200
">
    Botón
</button>
```

### Inputs con Focus

```html
<input class="
    border border-gray-300 
    rounded-lg 
    focus:ring-2 
    focus:ring-primary-500 
    focus:border-primary-500
    transition
">
```

### Cards con Sombras

```html
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <!-- Contenido -->
</div>
```

## 🎯 Utilidades Comunes de Tailwind

### Espaciado
- `p-{n}` - Padding (p-4, p-6, p-8)
- `m-{n}` - Margin (m-2, m-4, mb-6)
- `space-y-{n}` - Espacio vertical entre hijos
- `gap-{n}` - Gap en flex/grid

### Layout
- `flex` - Display flex
- `flex-col` - Dirección columna
- `items-center` - Alinear items centro
- `justify-between` - Justificar espacio entre
- `grid grid-cols-{n}` - Grid con n columnas

### Tipografía
- `text-{size}` - Tamaño (text-sm, text-lg, text-2xl)
- `font-{weight}` - Peso (font-medium, font-bold)
- `text-{color}` - Color del texto

### Colores de Estado
- `text-green-600` - Éxito
- `text-red-600` - Error
- `text-yellow-600` - Advertencia
- `text-primary-600` - Principal

### Responsive Design
```html
<div class="
    grid 
    grid-cols-1          <!-- Mobile: 1 columna -->
    md:grid-cols-2       <!-- Tablet: 2 columnas -->
    lg:grid-cols-3       <!-- Desktop: 3 columnas -->
">
```

## 🔄 Workflow de Desarrollo

1. **Edita vistas Blade** - Agrega clases de Tailwind
2. **Vite compila automáticamente** - Hot reload activo
3. **Ver cambios en tiempo real** - No necesitas recargar manualmente

## 🛠️ Comandos Útiles

```bash
# Iniciar Vite (desarrollo)
npm run dev

# Compilar para producción
npm run build

# Servidor Laravel
php artisan serve
```

## 📝 Mejores Prácticas

### ✅ Hacer
- Usar clases de Tailwind directamente en el HTML
- Reutilizar componentes Blade para lógica repetida
- Mantener colores consistentes usando `primary-*`
- Usar transiciones para efectos suaves

### ❌ Evitar
- CSS personalizado innecesario
- Colores hardcodeados (usa `primary`, `accent`)
- Duplicar componentes similares
- Clases inline extremadamente largas (crea componente)

## 🎨 Extensión del Sistema

### Agregar Nueva Variante de Color

```javascript
// En app.blade.php
colors: {
    primary: { ... },
    accent: { ... },
    tertiary: {  // Nueva variante
        500: '#...',
        600: '#...',
    }
}
```

### Agregar Nuevo Componente

```bash
# Crear archivo
touch resources/views/components/mi-componente.blade.php

# Usar en vistas
<x-mi-componente />
```

## 🔗 Recursos Adicionales

- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Laravel Blade Components](https://laravel.com/docs/blade#components)
- [Vite + Laravel](https://laravel.com/docs/vite)
