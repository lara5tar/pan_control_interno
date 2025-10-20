# JavaScript Puro (Vanilla JS) - Control Interno

## 🎯 Filosofía del Sistema

Este proyecto usa **JavaScript Puro (Vanilla JS)** sin frameworks ni librerías adicionales como:
- ❌ No Livewire
- ❌ No Alpine.js
- ❌ No jQuery
- ❌ No Vue.js / React
- ✅ Solo JavaScript ES6+ nativo del navegador

## 📁 Estructura de JavaScript

```
resources/
├── js/
│   ├── app.js          # JavaScript principal con utilidades
│   └── bootstrap.js    # Solo Axios para AJAX (opcional)
└── views/
    └── components/     # Componentes con JS inline
```

## 🔧 Funcionalidades Implementadas

### 1. Sistema de Alertas Auto-Dismissible

```javascript
// Auto-cierra alertas después de 5 segundos
const alerts = document.querySelectorAll('.alert-dismissible');
alerts.forEach(alert => {
    setTimeout(() => alert.remove(), 5000);
});
```

**Uso en Blade:**
```blade
<x-alert type="success" message="Operación exitosa" />
```

### 2. Validación de Formularios en Tiempo Real

```javascript
// Validación automática en blur
inputs.forEach(input => {
    input.addEventListener('blur', function() {
        validateInput(this);
    });
});
```

**Uso en HTML:**
```html
<form data-validate>
    <input type="email" required>
    <input type="number" min="0" required>
</form>
```

### 3. Confirmación de Eliminación

**Opción 1: Inline simple**
```html
<button onclick="return confirm('¿Estás seguro?')">
    Eliminar
</button>
```

**Opción 2: Dialog personalizado**
```html
<button onclick="confirmDialog.show('¿Eliminar este registro?', (confirmed) => {
    if (confirmed) document.getElementById('form-delete').submit();
})">
    Eliminar
</button>
```

### 4. Loading Spinner

```javascript
// Mostrar spinner
loadingSpinner.show('Procesando...');

// Ocultar spinner
loadingSpinner.hide();
```

**Auto-activación en formularios:**
```html
<form action="/submit">
    <!-- Se muestra automáticamente al enviar -->
</form>

<!-- Para desactivar el loading -->
<form action="/search" data-no-loading>
    <!-- No muestra spinner al enviar -->
</form>
```

### 5. Modales Reutilizables

```blade
<x-modal id="miModal" title="Título del Modal">
    <p>Contenido del modal</p>
</x-modal>

<!-- Botón para abrir -->
<button onclick="openModal('miModal')">Abrir Modal</button>
```

### 6. Notificaciones Toast

```javascript
showNotification('Mensaje guardado', 'success');
showNotification('Error al procesar', 'error');
showNotification('Advertencia', 'warning');
showNotification('Información', 'info');
```

### 7. Copiar al Portapapeles

```html
<button data-copy="Texto a copiar">
    Copiar código
</button>
```

### 8. Tooltips

```html
<span data-tooltip="Información adicional">
    Hover aquí
</span>
```

### 9. Utilidades de Tabla

```javascript
// Ordenar tabla
TableUtils.sortTable('miTabla', 0, true); // columna 0, ascendente

// Filtrar tabla
TableUtils.filterTable('miTabla', 'término de búsqueda');
```

### 10. Utilidades de Formulario

```javascript
// Limpiar formulario
FormUtils.clearForm('miFormulario');

// Deshabilitar formulario
FormUtils.disableForm('miFormulario');

// Habilitar formulario
FormUtils.enableForm('miFormulario');
```

## 📝 Componentes JavaScript Disponibles

### Alert con Botón de Cierre
```blade
<x-alert type="success" message="Mensaje" />
```
- Auto-cierre en 5 segundos
- Botón X para cerrar manualmente
- Animación de fade out

### Loading Spinner
```blade
<x-loading />
```
- Se activa automáticamente en envío de formularios
- Control manual con `loadingSpinner.show()` y `hide()`

### Confirm Dialog
```blade
<x-confirm-dialog />
```
- Dialog de confirmación personalizado
- Callbacks para manejar respuesta
- Cierre con ESC

### Modal
```blade
<x-modal id="uniqueId" title="Título">
    Contenido
</x-modal>
```
- Cierre con ESC o clic fuera
- Bloqueo de scroll del body
- Reutilizable

## 🎨 Eventos Personalizados

### Prevenir Salida con Cambios Sin Guardar

```javascript
// Automático en todos los formularios
// Detecta cambios y pregunta antes de salir
window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        return '';
    }
});
```

### Búsqueda con Debounce

```html
<input data-search type="text" placeholder="Buscar...">
```

Espera 300ms después de la última tecla presionada.

## 💡 Patrones Recomendados

### 1. Confirmación Antes de Acción

```html
<!-- Simple -->
<form onsubmit="return confirm('¿Continuar?')">

<!-- Con callback -->
<button onclick="confirmDialog.show('¿Eliminar?', (ok) => {
    if (ok) this.closest('form').submit();
})">
```

### 2. Envío AJAX (opcional con Axios)

```javascript
// Si necesitas AJAX
axios.post('/api/endpoint', { data })
    .then(response => {
        showNotification('Éxito', 'success');
    })
    .catch(error => {
        showNotification('Error', 'error');
    });
```

### 3. Manipulación del DOM

```javascript
// Seleccionar elementos
const element = document.getElementById('id');
const elements = document.querySelectorAll('.class');

// Modificar clases
element.classList.add('class');
element.classList.remove('class');
element.classList.toggle('class');

// Modificar contenido
element.textContent = 'Texto';
element.innerHTML = '<b>HTML</b>';

// Crear elementos
const div = document.createElement('div');
div.className = 'mi-clase';
document.body.appendChild(div);
```

### 4. Event Listeners

```javascript
// Event delegation (recomendado)
document.addEventListener('click', function(e) {
    if (e.target.matches('.mi-boton')) {
        // Manejar click
    }
});

// Event listener directo
element.addEventListener('click', function() {
    // Manejar click
});
```

## 🚫 Lo Que NO Usamos

### ❌ jQuery
```javascript
// NO: $('#id').show();
// SI: document.getElementById('id').classList.remove('hidden');
```

### ❌ Alpine.js
```html
<!-- NO: <div x-data="{ open: false }"> -->
<!-- SI: <div id="menu"> + JavaScript -->
```

### ❌ Livewire
```php
// NO: wire:click="delete"
// SI: onclick="confirmDialog.show(...)"
```

## ✅ Ventajas de JavaScript Puro

1. **Sin dependencias** - Menos peso, más rápido
2. **Mejor rendimiento** - Sin overhead de frameworks
3. **Control total** - Sabes exactamente qué hace el código
4. **Estándares web** - Código que funcionará siempre
5. **Fácil debug** - Sin capas de abstracción
6. **Aprendizaje** - Mejoras tus skills de JS vanilla

## 📚 Recursos para Aprender

- [MDN Web Docs](https://developer.mozilla.org/es/docs/Web/JavaScript)
- [JavaScript.info](https://javascript.info/)
- [You Might Not Need jQuery](http://youmightnotneedjquery.com/)
- [Vanilla JS Toolkit](https://vanillajstoolkit.com/)

## 🎯 Guía Rápida de Migración

### De jQuery a Vanilla JS

| jQuery | Vanilla JS |
|--------|-----------|
| `$(selector)` | `document.querySelector(selector)` |
| `$(selector).hide()` | `element.classList.add('hidden')` |
| `$(selector).show()` | `element.classList.remove('hidden')` |
| `$.ajax()` | `fetch()` o `axios` |
| `$(selector).on('click')` | `element.addEventListener('click')` |
| `$(selector).val()` | `element.value` |
| `$(selector).text()` | `element.textContent` |
| `$(selector).html()` | `element.innerHTML` |

## 🔧 Tips y Tricks

### Esperar a que el DOM esté listo
```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Tu código aquí
});
```

### Crear función helper reutilizable
```javascript
function $(selector) {
    return document.querySelector(selector);
}

function $$(selector) {
    return document.querySelectorAll(selector);
}
```

### Event delegation para elementos dinámicos
```javascript
document.addEventListener('click', function(e) {
    if (e.target.matches('.delete-btn')) {
        // Funciona incluso para elementos agregados después
    }
});
```

## 📌 Resumen

✅ **JavaScript Puro (ES6+)**
✅ **Tailwind CSS** para estilos
✅ **Blade** para templates
✅ **Axios** (opcional) para AJAX
❌ **Sin Livewire**
❌ **Sin Alpine**
❌ **Sin jQuery**
❌ **Sin frameworks JS adicionales**

Todo el código JavaScript está en `resources/js/app.js` y componentes Blade con `<script>` inline.
