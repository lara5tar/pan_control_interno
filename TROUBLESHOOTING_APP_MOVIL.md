# 🔧 GUÍA DE TROUBLESHOOTING - ERRORES EN APP MÓVIL

## ❌ Errores Comunes y Sus Soluciones

### 1. Error: "Failed to connect" o "Network Error"

**Causa:** La app no puede conectarse al servidor.

**Soluciones:**

**a) Para Android Emulator:**
```dart
// ❌ NO usar localhost o 127.0.0.1
static const String baseUrl = 'http://localhost:8000/api/v1/movil';

// ✅ USA 10.0.2.2
static const String baseUrl = 'http://10.0.2.2:8000/api/v1/movil';
```

**b) Para iOS Simulator:**
```dart
// ✅ Puedes usar localhost
static const String baseUrl = 'http://localhost:8000/api/v1/movil';
// o
static const String baseUrl = 'http://127.0.0.1:8000/api/v1/movil';
```

**c) Para Dispositivo Físico:**
```dart
// Usa la IP de tu computadora en la red local
static const String baseUrl = 'http://192.168.1.100:8000/api/v1/movil';
```

**d) Verifica que el servidor esté corriendo:**
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

---

### 2. Error 404: "Not Found"

**Causa:** La URL de la ruta está incorrecta.

**URLs Correctas:**
```
✅ http://tu-dominio.com/api/v1/movil/apartados
✅ http://tu-dominio.com/api/v1/movil/apartados/buscar-folio/AP-2026-0001
✅ http://tu-dominio.com/api/v1/movil/apartados/buscar-cliente?nombre=Juan
✅ http://tu-dominio.com/api/v1/movil/apartados/3/abonos
✅ http://tu-dominio.com/api/v1/movil/abonos

❌ http://tu-dominio.com/api/movil/apartados (falta v1)
❌ http://tu-dominio.com/api/v1/apartados (falta movil)
❌ http://tu-dominio.com/api/v1/movil/buscar-folio/... (falta apartados/)
```

**Verificación en tu código:**
```dart
// Asegúrate de que tu baseUrl sea correcto
static const String baseUrl = 'http://tu-dominio.com/api/v1/movil';

// Y que las rutas NO repitan /api/v1/movil
final uri = Uri.parse('$baseUrl/apartados'); // ✅ Correcto
final uri = Uri.parse('$baseUrl/api/v1/movil/apartados'); // ❌ Duplicado
```

---

### 3. Error 400: "Nombre no proporcionado" (Búsqueda por Cliente)

**Causa:** Falta el parámetro `nombre` en la búsqueda.

**Código Incorrecto:**
```dart
// ❌ Sin parámetro nombre
final uri = Uri.parse('$baseUrl/apartados/buscar-cliente');
```

**Código Correcto:**
```dart
// ✅ Con parámetro nombre
final uri = Uri.parse('$baseUrl/apartados/buscar-cliente')
    .replace(queryParameters: {'nombre': 'Juan'});
```

---

### 4. Error 422: Validation Error (POST Abono)

**Causa:** Datos de validación incorrectos.

**Verifica que:**

1. **Todos los campos requeridos estén presentes:**
```dart
// ✅ Correcto
{
  'apartado_id': 3,           // ✅ Requerido
  'monto': 150.00,            // ✅ Requerido (debe ser > 0)
  'metodo_pago': 'efectivo',  // ✅ Requerido (efectivo/transferencia/tarjeta)
  'usuario': 'juan_perez'     // ✅ Requerido
}

// ❌ Incorrecto
{
  'apartado_id': 3,
  'monto': 150.00,
  // ❌ Falta metodo_pago
  // ❌ Falta usuario
}
```

2. **El método de pago sea válido:**
```dart
// ✅ Válidos
'efectivo'
'transferencia'
'tarjeta'

// ❌ Inválidos
'Efectivo'    // Mayúscula
'credito'     // No existe
'cash'        // En inglés
```

3. **El monto sea mayor a 0:**
```dart
'monto': 150.00  // ✅
'monto': 0       // ❌
'monto': -50     // ❌
```

---

### 5. Error 400: "El monto del abono excede el saldo pendiente"

**Causa:** Estás intentando abonar más de lo que debe el apartado.

**Solución:**
```dart
// Antes de registrar el abono, verifica el saldo
if (montoAbono > apartado['saldo_pendiente']) {
  // Mostrar error al usuario
  showDialog(
    context: context,
    builder: (context) => AlertDialog(
      title: Text('Error'),
      content: Text(
        'El monto ($montoAbono) excede el saldo pendiente '
        '(${apartado["saldo_pendiente"]})'
      ),
    ),
  );
  return;
}
```

---

### 6. Error 400: "Este apartado está cancelado/liquidado"

**Causa:** El apartado no puede recibir más abonos.

**Solución:**
```dart
// Verifica el estado antes de permitir abonar
if (apartado['estado'] == 'cancelado') {
  showError('Este apartado está cancelado y no puede recibir abonos');
  return;
}

if (apartado['estado'] == 'liquidado') {
  showError('Este apartado ya está completamente pagado');
  return;
}

// Solo permite abonar si está activo o vencido
if (apartado['estado'] == 'activo' || apartado['estado'] == 'vencido') {
  // Permitir registrar abono
}
```

---

### 7. Error: "FormatException: Unexpected character"

**Causa:** El servidor está devolviendo HTML en lugar de JSON.

**Esto puede suceder si:**
- El servidor Laravel tiene un error 500
- La ruta no existe (404 page)
- Hay un problema de CORS

**Solución:**

1. **Prueba la URL en el navegador:**
```
http://tu-ip:8000/api/v1/movil/apartados
```

2. **Verifica los logs de Laravel:**
```bash
tail -f storage/logs/laravel.log
```

3. **Asegúrate de enviar el header Accept:**
```dart
final response = await http.get(
  uri,
  headers: {
    'Accept': 'application/json', // ✅ Importante
  },
);
```

---

### 8. Error: "Connection refused"

**Causa:** El servidor no está corriendo.

**Solución:**

1. **Inicia el servidor:**
```bash
cd /Users/usuario/Desktop/pan_control_interno
php artisan serve --host=0.0.0.0 --port=8000
```

2. **Verifica que esté corriendo:**
```bash
curl http://localhost:8000/api/v1/movil/apartados
```

---

### 9. Error: "No se encontraron apartados" (404)

**Causa:** No hay datos en la base de datos.

**Solución:**

1. **Verifica que haya apartados en la BD:**
```bash
php artisan tinker
>>> App\Models\Apartado::count()
```

2. **Si no hay datos, crea algunos de prueba:**
```bash
php artisan db:seed
# o crea manualmente desde la app web
```

---

### 10. Headers CORS Error

**Causa:** El servidor no permite peticiones desde tu app.

**Solución:**

Instala el paquete CORS de Laravel (si no está instalado):
```bash
composer require fruitcake/laravel-cors
```

Configura en `config/cors.php`:
```php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],  // En producción usa dominios específicos
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

---

## 🧪 CÓDIGO DE PRUEBA FLUTTER

Usa este código para probar cada endpoint:

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiTester {
  // Cambia por tu IP
  static const String baseUrl = 'http://10.0.2.2:8000/api/v1/movil';
  
  static Future<void> testAllEndpoints() async {
    print('========== TESTING API ==========');
    
    // Test 1: Listar apartados
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/apartados'),
        headers: {'Accept': 'application/json'},
      );
      print('✅ GET /apartados: ${response.statusCode}');
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        print('   Total apartados: ${data['total']}');
      }
    } catch (e) {
      print('❌ GET /apartados: $e');
    }
    
    // Test 2: Buscar por folio
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/apartados/buscar-folio/AP-2026-0001'),
        headers: {'Accept': 'application/json'},
      );
      print('✅ GET /buscar-folio: ${response.statusCode}');
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        print('   Folio encontrado: ${data['data']['folio']}');
      }
    } catch (e) {
      print('❌ GET /buscar-folio: $e');
    }
    
    // Test 3: Buscar por cliente
    try {
      final uri = Uri.parse('$baseUrl/apartados/buscar-cliente')
          .replace(queryParameters: {'nombre': 'Clientes'});
      final response = await http.get(
        uri,
        headers: {'Accept': 'application/json'},
      );
      print('✅ GET /buscar-cliente: ${response.statusCode}');
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        print('   Clientes encontrados: ${data['data'].length}');
      }
    } catch (e) {
      print('❌ GET /buscar-cliente: $e');
    }
    
    // Test 4: Historial de abonos
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/apartados/3/abonos'),
        headers: {'Accept': 'application/json'},
      );
      print('✅ GET /apartados/3/abonos: ${response.statusCode}');
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        print('   Abonos: ${data['data']['abonos'].length}');
      }
    } catch (e) {
      print('❌ GET /apartados/3/abonos: $e');
    }
    
    print('========== TESTS COMPLETED ==========');
  }
}

// Llama esto desde tu app
void main() async {
  await ApiTester.testAllEndpoints();
}
```

---

## 📋 CHECKLIST DE VERIFICACIÓN

Antes de reportar un error, verifica:

- [ ] El servidor está corriendo (`php artisan serve`)
- [ ] La URL base es correcta (incluye `/api/v1/movil`)
- [ ] Usas la IP correcta (10.0.2.2 para Android Emulator)
- [ ] Envías el header `Accept: application/json`
- [ ] Para POST, envías `Content-Type: application/json`
- [ ] Los parámetros requeridos están presentes
- [ ] Los valores de los campos son válidos
- [ ] Puedes acceder a la URL desde el navegador

---

## 🆘 ÚLTIMA OPCIÓN: PRUEBA CON CURL

Si todo falla, prueba primero con curl para asegurarte de que el servidor funciona:

```bash
# Test 1
curl -v http://localhost:8000/api/v1/movil/apartados

# Test 2
curl -v http://localhost:8000/api/v1/movil/apartados/buscar-folio/AP-2026-0001

# Test 3
curl -v "http://localhost:8000/api/v1/movil/apartados/buscar-cliente?nombre=Clientes"

# Test 4
curl -v http://localhost:8000/api/v1/movil/apartados/3/abonos

# Test 5
curl -v -X POST http://localhost:8000/api/v1/movil/abonos \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "apartado_id": 3,
    "monto": 50,
    "metodo_pago": "efectivo",
    "usuario": "test"
  }'
```

Si curl funciona pero tu app no, el problema está en el código de la app, no en el servidor.
