# Configuración para Despliegue en Producción

## Variables de Entorno (.env en producción)

Asegúrate de actualizar estas variables en tu archivo `.env` del servidor de producción:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pan.110694.xyz

# Resto de tu configuración...
```

## Después de subir cambios al servidor

Ejecuta estos comandos en el servidor:

```bash
# Limpiar caché de configuración
php artisan config:cache

# Limpiar caché de rutas
php artisan route:cache

# Limpiar caché de vistas
php artisan view:cache

# Optimizar autoload
composer dump-autoload -o
```

## Verificación

1. Las URLs ahora deberían generarse automáticamente con HTTPS
2. Los exports de Excel/PDF usarán HTTPS
3. Todos los enlaces y rutas usarán HTTPS

## Notas Importantes

- El código detecta automáticamente si está en producción
- También detecta si está detrás de un proxy/load balancer
- En local (localhost) seguirá usando HTTP normalmente
