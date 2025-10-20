# Guía de Personalización de Colores con Tailwind CSS

## 📍 Ubicación del Archivo de Configuración

El sistema ahora usa **Tailwind CSS** con colores personalizados. La configuración se encuentra en:

```
resources/views/layouts/app.blade.php
```

Dentro del tag `<script>` en la sección `tailwind.config`.

## 🎨 Cómo Cambiar los Colores

### Configuración Actual de Tailwind

Los colores están definidos en el layout principal (`app.blade.php`):

```javascript
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#f0f9ff',
                    100: '#e0f2fe',
                    200: '#bae6fd',
                    300: '#7dd3fc',
                    400: '#38bdf8',
                    500: '#0ea5e9',  // Color principal (azul claro)
                    600: '#0284c7',  // Hover y estados
                    700: '#0369a1',  // Activo y pressed
                    800: '#075985',
                    900: '#0c4a6e',
                },
                accent: {
                    400: '#fcd34d',
                    500: '#fbbf24',  // Color de acento (amarillo)
                    600: '#f59e0b',
                }
            }
        }
    }
}
```

### Clases de Tailwind Utilizadas

El sistema usa las siguientes clases de Tailwind con el color personalizado:

- **Backgrounds**: `bg-primary-500`, `bg-primary-600`, `bg-primary-700`
- **Text colors**: `text-primary-500`, `text-primary-600`
- **Borders**: `border-primary-500`, `border-accent-500`
- **Hover states**: `hover:bg-primary-600`, `hover:text-primary-700`
- **Focus states**: `focus:ring-primary-500`, `focus:border-primary-500`
- **Gradients**: `from-primary-500 to-primary-600`

### Ejemplos de Paletas de Colores

#### Azul Claro (Actual - Sky Blue)
```javascript
primary: {
    500: '#0ea5e9',
    600: '#0284c7',
    700: '#0369a1',
}
```

#### Verde Esmeralda (Emerald)
```javascript
primary: {
    500: '#10b981',
    600: '#059669',
    700: '#047857',
}
```

#### Púrpura (Purple)
```javascript
primary: {
    500: '#8b5cf6',
    600: '#7c3aed',
    700: '#6d28d9',
}
```

#### Azul Oscuro (Blue)
```javascript
primary: {
    500: '#3b82f6',
    600: '#2563eb',
    700: '#1d4ed8',
}
```

#### Rojo/Rosa (Pink)
```javascript
primary: {
    500: '#ec4899',
    600: '#db2777',
    700: '#be185d',
}
```

#### Naranja (Orange)
```javascript
primary: {
    500: '#f97316',
    600: '#ea580c',
    700: '#c2410c',
}
```

## 🔧 Cómo Aplicar los Cambios

1. Abre el archivo `resources/views/layouts/app.blade.php`
2. Busca la sección `<script>` con `tailwind.config`
3. Modifica los valores hexadecimales en la sección `primary`
4. Guarda el archivo
5. Recarga el navegador (Ctrl+F5 para limpiar caché)

**Ejemplo de cambio a Verde Esmeralda:**

```javascript
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#34d399',
                    500: '#10b981',  // Color principal
                    600: '#059669',  // Hover
                    700: '#047857',  // Activo
                    800: '#065f46',
                    900: '#064e3b',
                },
                // ... accent permanece igual
            }
        }
    }
}
```

## 📦 Componentes que Usan los Colores Personalizados

Todos los componentes ahora usan clases de Tailwind CSS con el color `primary`:

- **Sidebar** (Menú lateral) - `bg-gradient-to-b from-primary-500 to-primary-600`
- **Menu Items** - `hover:bg-primary-600`, `bg-primary-700` (activo)
- **Botones primarios** - `bg-primary-500 hover:bg-primary-600`
- **Links principales** - `text-primary-500 hover:text-primary-700`
- **Inputs en focus** - `focus:ring-primary-500 focus:border-primary-500`
- **Badges** - `bg-primary-100 text-primary-600`
- **Bordes activos** - `border-accent-500`

## 💡 Ventajas de Usar Tailwind CSS

✅ **Colores consistentes** en toda la aplicación
✅ **Fácil de personalizar** - solo cambia los valores hex
✅ **Autocomplete** en el editor
✅ **Utilidades built-in** para hover, focus, active
✅ **Responsive** por defecto
✅ **Sin CSS personalizado** extra necesario

## 🔍 Referencia Rápida de Clases

| Propósito | Clase Tailwind | Uso |
|-----------|---------------|-----|
| Fondo primario | `bg-primary-500` | Botones, tarjetas |
| Fondo hover | `bg-primary-600` | Estados hover |
| Fondo activo | `bg-primary-700` | Estados activos/pressed |
| Texto primario | `text-primary-500` | Links, títulos |
| Borde | `border-primary-500` | Inputs, dividers |
| Ring (focus) | `ring-primary-500` | Inputs focus |
| Gradiente | `from-primary-500 to-primary-600` | Fondos decorativos |

## 💡 Herramientas Útiles

Para generar paletas de colores armoniosas:
- **Tailwind Colors**: https://tailwindcss.com/docs/customizing-colors
- **Coolors**: https://coolors.co/
- **Adobe Color**: https://color.adobe.com/
- **UI Colors**: https://uicolors.app/create (Generador de paletas Tailwind)

## 📝 Notas Importantes

1. **Contraste**: Asegúrate de que los colores tengan buen contraste con texto blanco
2. **Consistencia**: Usa siempre `primary-500` como base, `primary-600` para hover, y `primary-700` para activo
3. **Degradados**: Para gradientes usa `from-primary-X to-primary-Y`
4. **No edites** directamente las clases en los componentes, cambia solo la configuración central
