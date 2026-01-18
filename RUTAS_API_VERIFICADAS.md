# ✅ RUTAS API VERIFICADAS - ABONOS MÓVIL

**Fecha de verificación:** 17 de enero de 2026  
**Base URL:** `http://tu-dominio.com/api/v1/movil`

---

## 📋 TABLA DE RUTAS

| # | Método | Ruta Completa | Parámetros | Estado |
|---|--------|---------------|------------|--------|
| 1 | `GET` | `/api/v1/movil/apartados` | `?estado=activo&limite=50` (opcionales) | ✅ FUNCIONA |
| 2 | `GET` | `/api/v1/movil/apartados/buscar-folio/{folio}` | `{folio}` en URL | ✅ FUNCIONA |
| 3 | `GET` | `/api/v1/movil/apartados/buscar-cliente` | `?nombre=texto` (requerido) | ✅ FUNCIONA |
| 4 | `GET` | `/api/v1/movil/apartados/{apartado_id}/abonos` | `{apartado_id}` en URL | ✅ FUNCIONA |
| 5 | `POST` | `/api/v1/movil/abonos` | JSON en body | ✅ FUNCIONA |

---

## 🔗 EJEMPLOS DE URLS COMPLETAS

### Para Desarrollo Local:
```
http://127.0.0.1:8000/api/v1/movil/apartados
http://127.0.0.1:8000/api/v1/movil/apartados/buscar-folio/AP-2026-0001
http://127.0.0.1:8000/api/v1/movil/apartados/buscar-cliente?nombre=Juan
http://127.0.0.1:8000/api/v1/movil/apartados/3/abonos
http://127.0.0.1:8000/api/v1/movil/abonos (POST)
```

### Para Producción:
```
https://tu-dominio.com/api/v1/movil/apartados
https://tu-dominio.com/api/v1/movil/apartados/buscar-folio/AP-2026-0001
https://tu-dominio.com/api/v1/movil/apartados/buscar-cliente?nombre=Juan
https://tu-dominio.com/api/v1/movil/apartados/3/abonos
https://tu-dominio.com/api/v1/movil/abonos (POST)
```

---

## 📝 DETALLES DE CADA ENDPOINT

### 1️⃣ Listar Apartados
```
GET /api/v1/movil/apartados
```

**Query Parameters (opcionales):**
- `estado`: `activo` | `vencido` | `liquidado` | `todos`
- `limite`: número (default: 50)

**Ejemplos:**
```bash
# Todos los apartados activos/vencidos
curl http://localhost:8000/api/v1/movil/apartados

# Solo activos
curl http://localhost:8000/api/v1/movil/apartados?estado=activo

# Primeros 10
curl http://localhost:8000/api/v1/movil/apartados?limite=10

# Todos (incluyendo liquidados)
curl http://localhost:8000/api/v1/movil/apartados?estado=todos
```

**Headers requeridos:**
```
Accept: application/json
```

---

### 2️⃣ Buscar por Folio
```
GET /api/v1/movil/apartados/buscar-folio/{folio}
```

**Path Parameters:**
- `{folio}`: Número de folio del apartado (ej: AP-2026-0001)

**Ejemplo:**
```bash
curl http://localhost:8000/api/v1/movil/apartados/buscar-folio/AP-2026-0001
```

**Headers requeridos:**
```
Accept: application/json
```

---

### 3️⃣ Buscar por Cliente
```
GET /api/v1/movil/apartados/buscar-cliente
```

**Query Parameters:**
- `nombre`: Nombre o parte del nombre del cliente (REQUERIDO)

**Ejemplos:**
```bash
# Buscar "Juan"
curl "http://localhost:8000/api/v1/movil/apartados/buscar-cliente?nombre=Juan"

# Buscar "María"
curl "http://localhost:8000/api/v1/movil/apartados/buscar-cliente?nombre=María"

# Buscar parcial
curl "http://localhost:8000/api/v1/movil/apartados/buscar-cliente?nombre=Cli"
```

**Headers requeridos:**
```
Accept: application/json
```

**⚠️ IMPORTANTE:** El parámetro `nombre` es REQUERIDO. Si no se envía, devuelve error 400.

---

### 4️⃣ Historial de Abonos
```
GET /api/v1/movil/apartados/{apartado_id}/abonos
```

**Path Parameters:**
- `{apartado_id}`: ID numérico del apartado (ej: 3)

**Ejemplo:**
```bash
curl http://localhost:8000/api/v1/movil/apartados/3/abonos
```

**Headers requeridos:**
```
Accept: application/json
```

---

### 5️⃣ Registrar Abono
```
POST /api/v1/movil/abonos
```

**Headers requeridos:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "apartado_id": 3,
  "monto": 150.00,
  "metodo_pago": "efectivo",
  "comprobante": "REF123",
  "observaciones": "Abono desde app",
  "usuario": "nombre_usuario"
}
```

**Campos requeridos:**
- `apartado_id` (integer)
- `monto` (decimal > 0)
- `metodo_pago` (string: `efectivo`, `transferencia`, `tarjeta`)
- `usuario` (string)

**Campos opcionales:**
- `comprobante` (string)
- `observaciones` (string, máx 500 caracteres)

**Ejemplo:**
```bash
curl -X POST http://localhost:8000/api/v1/movil/abonos \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "apartado_id": 3,
    "monto": 150.00,
    "metodo_pago": "transferencia",
    "comprobante": "REF123456",
    "observaciones": "Abono realizado desde app móvil",
    "usuario": "maria_lopez"
  }'
```

---

## 🔍 ERRORES COMUNES Y SOLUCIONES

### Error 404 - Not Found
**Problema:** La ruta no existe o está mal escrita.

**Soluciones:**
- ✅ Verifica que la URL incluya `/api/v1/movil/`
- ✅ Revisa que no haya espacios o caracteres especiales en la URL
- ✅ Para búsqueda por folio, usa la ruta completa: `/apartados/buscar-folio/{folio}`

**Ejemplo correcto:**
```
❌ /api/movil/apartados
✅ /api/v1/movil/apartados

❌ /api/v1/movil/buscar-folio/AP-2026-0001
✅ /api/v1/movil/apartados/buscar-folio/AP-2026-0001
```

### Error 400 - Bad Request
**Problema:** Parámetro requerido faltante o apartado en estado incorrecto.

**Soluciones:**
- ✅ En búsqueda por cliente, asegúrate de enviar `?nombre=texto`
- ✅ Verifica que el apartado esté en estado `activo` o `vencido`
- ✅ Asegúrate de que el monto del abono no exceda el saldo pendiente

### Error 422 - Validation Error
**Problema:** Datos de validación incorrectos en POST.

**Soluciones:**
- ✅ Verifica que todos los campos requeridos estén presentes
- ✅ El `monto` debe ser mayor a 0
- ✅ El `metodo_pago` debe ser: `efectivo`, `transferencia` o `tarjeta`
- ✅ El `apartado_id` debe existir en la base de datos

### Error 500 - Server Error
**Problema:** Error en el servidor.

**Soluciones:**
- ✅ Verifica los logs de Laravel
- ✅ Asegúrate de que la base de datos esté corriendo
- ✅ Revisa que las relaciones (cliente, detalles, libros) existan

---

## 🧪 PRUEBAS REALIZADAS

Todas las rutas fueron probadas el 17/01/2026 con los siguientes resultados:

| Endpoint | Código HTTP | Respuesta | Estado |
|----------|-------------|-----------|--------|
| `GET /apartados` | 200 | JSON con 3 apartados | ✅ OK |
| `GET /apartados/buscar-folio/AP-2026-0001` | 200 | JSON con apartado | ✅ OK |
| `GET /apartados/buscar-cliente?nombre=Clientes` | 200 | JSON con 1 cliente | ✅ OK |
| `GET /apartados/3/abonos` | 200 | JSON con historial | ✅ OK |
| `POST /abonos` | 201 | JSON con abono creado | ✅ OK |

---

## 📱 CONFIGURACIÓN EN APP MÓVIL

### Flutter/Dart

```dart
class AbonosApiService {
  // Cambia esto por tu dominio real
  static const String baseUrl = 'http://tu-dominio.com/api/v1/movil';
  
  // Para desarrollo local:
  // static const String baseUrl = 'http://10.0.2.2:8000/api/v1/movil'; // Android Emulator
  // static const String baseUrl = 'http://127.0.0.1:8000/api/v1/movil'; // iOS Simulator
  
  Future<Map<String, dynamic>> listarApartados({String? estado, int limite = 50}) async {
    final queryParams = <String, String>{};
    if (estado != null) queryParams['estado'] = estado;
    queryParams['limite'] = limite.toString();
    
    final uri = Uri.parse('$baseUrl/apartados').replace(queryParameters: queryParams);
    
    final response = await http.get(
      uri,
      headers: {'Accept': 'application/json'},
    );
    
    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Error al listar apartados: ${response.statusCode}');
    }
  }
  
  Future<Map<String, dynamic>> buscarPorFolio(String folio) async {
    final uri = Uri.parse('$baseUrl/apartados/buscar-folio/$folio');
    
    final response = await http.get(
      uri,
      headers: {'Accept': 'application/json'},
    );
    
    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Error al buscar por folio: ${response.statusCode}');
    }
  }
  
  Future<Map<String, dynamic>> registrarAbono({
    required int apartadoId,
    required double monto,
    required String metodoPago,
    String? comprobante,
    String? observaciones,
    required String usuario,
  }) async {
    final uri = Uri.parse('$baseUrl/abonos');
    
    final response = await http.post(
      uri,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: json.encode({
        'apartado_id': apartadoId,
        'monto': monto,
        'metodo_pago': metodoPago,
        if (comprobante != null) 'comprobante': comprobante,
        if (observaciones != null) 'observaciones': observaciones,
        'usuario': usuario,
      }),
    );
    
    if (response.statusCode == 201 || response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      final error = json.decode(response.body);
      throw Exception(error['message'] ?? 'Error al registrar abono');
    }
  }
}
```

### JavaScript/React Native

```javascript
class AbonosApiService {
  // Cambia esto por tu dominio real
  static baseUrl = 'http://tu-dominio.com/api/v1/movil';
  
  static async listarApartados(estado = null, limite = 50) {
    let url = `${this.baseUrl}/apartados`;
    const params = new URLSearchParams();
    if (estado) params.append('estado', estado);
    params.append('limite', limite);
    
    if (params.toString()) url += '?' + params.toString();
    
    const response = await fetch(url, {
      headers: {
        'Accept': 'application/json',
      },
    });
    
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    
    return await response.json();
  }
  
  static async buscarPorFolio(folio) {
    const response = await fetch(
      `${this.baseUrl}/apartados/buscar-folio/${folio}`,
      {
        headers: {
          'Accept': 'application/json',
        },
      }
    );
    
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    
    return await response.json();
  }
  
  static async registrarAbono(data) {
    const response = await fetch(`${this.baseUrl}/abonos`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(data),
    });
    
    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Error al registrar abono');
    }
    
    return await response.json();
  }
}
```

---

## ⚠️ IMPORTANTE: URLs PARA TESTING

### Android Emulator:
```
http://10.0.2.2:8000/api/v1/movil/...
```

### iOS Simulator:
```
http://127.0.0.1:8000/api/v1/movil/...
```

### Dispositivo Físico en la misma red:
```
http://[IP-DE-TU-COMPUTADORA]:8000/api/v1/movil/...
```

Para encontrar tu IP:
- **macOS/Linux:** `ifconfig | grep inet`
- **Windows:** `ipconfig`

---

## 📞 SOPORTE

Si continúas teniendo errores:

1. **Verifica los logs de Laravel:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Verifica que el servidor esté corriendo:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

3. **Prueba con Postman o curl primero** antes de probar en la app

4. **Revisa la respuesta completa del servidor** para ver el error exacto
