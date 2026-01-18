# 📱 API CREAR CLIENTE - APP MÓVIL

## 🎯 ENDPOINTS DISPONIBLES

### 1. Crear Cliente
```
POST /api/v1/movil/clientes
```

### 2. Listar Clientes
```
GET /api/v1/movil/clientes
GET /api/v1/movil/clientes?busqueda={texto}
```

---

## 📋 1. CREAR CLIENTE

### Endpoint
```
POST /api/v1/movil/clientes
```

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Body (JSON)
```json
{
  "nombre": "Juan Pérez",          // Requerido, max 255 caracteres
  "telefono": "555-1234"           // Opcional, max 20 caracteres
}
```

### Respuesta Exitosa (201 Created)
```json
{
  "success": true,
  "message": "Cliente creado exitosamente",
  "data": {
    "id": 44,
    "nombre": "Juan Pérez",
    "telefono": "555-1234",
    "created_at": "2026-01-18 06:52:46",
    "es_nuevo": true
  }
}
```

### Respuesta: Cliente Ya Existe (200 OK)
```json
{
  "success": true,
  "message": "El cliente ya existe",
  "data": {
    "id": 44,
    "nombre": "Juan Pérez",
    "telefono": "555-1234",
    "created_at": "2026-01-18 06:52:46",
    "es_nuevo": false
  }
}
```

### Respuesta: Error de Validación (422 Unprocessable Entity)
```json
{
  "success": false,
  "message": "Errores de validación",
  "errors": {
    "nombre": ["El nombre del cliente es requerido"]
  }
}
```

---

## 📋 2. LISTAR CLIENTES

### Endpoint
```
GET /api/v1/movil/clientes
GET /api/v1/movil/clientes?busqueda={texto}
GET /api/v1/movil/clientes?busqueda={texto}&limite={numero}
```

### Parámetros Query (Opcionales)
- `busqueda`: Busca en nombre y teléfono
- `limite`: Límite de resultados (default: 100)

### Respuesta Exitosa (200 OK)
```json
{
  "success": true,
  "total": 3,
  "data": [
    {
      "id": 44,
      "nombre": "Juan Pérez",
      "telefono": "555-1234",
      "total_apartados": 2,
      "apartados_activos": 1
    },
    {
      "id": 45,
      "nombre": "María González",
      "telefono": null,
      "total_apartados": 0,
      "apartados_activos": 0
    }
  ]
}
```

---

## 🔍 VALIDACIONES

### Campo: `nombre`
- ✅ **Requerido**
- ✅ Tipo: String
- ✅ Máximo: 255 caracteres
- ❌ No puede estar vacío

### Campo: `telefono`
- ✅ **Opcional**
- ✅ Tipo: String
- ✅ Máximo: 20 caracteres
- ✅ Puede ser null

### Detección de Duplicados
El sistema detecta automáticamente si un cliente ya existe:
- Si hay teléfono: Busca por nombre + teléfono
- Si no hay teléfono: Busca solo por nombre (con teléfono null)

Si el cliente existe, devuelve el cliente existente con `es_nuevo: false`

---

## 🎨 CÓDIGO FLUTTER - SERVICIO

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class ClienteService {
  static const String baseUrl = 'http://10.0.2.2:8003/api/v1/movil';
  
  /// Crear un nuevo cliente
  static Future<Map<String, dynamic>> crearCliente({
    required String nombre,
    String? telefono,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/clientes'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode({
          'nombre': nombre.trim(),
          if (telefono != null && telefono.isNotEmpty) 
            'telefono': telefono.trim(),
        }),
      );
      
      final data = json.decode(response.body);
      
      if (response.statusCode == 201 || response.statusCode == 200) {
        return {
          'success': true,
          'data': data['data'],
          'message': data['message'],
          'es_nuevo': data['data']['es_nuevo'],
        };
      } else if (response.statusCode == 422) {
        // Errores de validación
        return {
          'success': false,
          'errors': data['errors'],
          'message': data['message'],
        };
      } else {
        return {
          'success': false,
          'message': data['message'],
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Error de conexión: $e',
      };
    }
  }
  
  /// Listar clientes
  static Future<Map<String, dynamic>> listarClientes({
    String? busqueda,
    int? limite,
  }) async {
    try {
      var uri = Uri.parse('$baseUrl/clientes');
      
      // Agregar parámetros de búsqueda
      if (busqueda != null && busqueda.isNotEmpty) {
        uri = uri.replace(queryParameters: {
          'busqueda': busqueda,
          if (limite != null) 'limite': limite.toString(),
        });
      }
      
      final response = await http.get(
        uri,
        headers: {'Accept': 'application/json'},
      );
      
      final data = json.decode(response.body);
      
      if (response.statusCode == 200) {
        return {
          'success': true,
          'total': data['total'],
          'data': data['data'],
        };
      } else {
        return {
          'success': false,
          'message': data['message'],
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Error de conexión: $e',
      };
    }
  }
}
```

---

## 🎨 CÓDIGO FLUTTER - WIDGET CREAR CLIENTE

```dart
import 'package:flutter/material.dart';

class CrearClienteScreen extends StatefulWidget {
  @override
  _CrearClienteScreenState createState() => _CrearClienteScreenState();
}

class _CrearClienteScreenState extends State<CrearClienteScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nombreController = TextEditingController();
  final _telefonoController = TextEditingController();
  
  bool _isLoading = false;
  
  Future<void> _crearCliente() async {
    if (!_formKey.currentState!.validate()) return;
    
    setState(() => _isLoading = true);
    
    final resultado = await ClienteService.crearCliente(
      nombre: _nombreController.text,
      telefono: _telefonoController.text.isEmpty 
          ? null 
          : _telefonoController.text,
    );
    
    setState(() => _isLoading = false);
    
    if (resultado['success']) {
      final cliente = resultado['data'];
      final esNuevo = resultado['es_nuevo'];
      
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: Text(esNuevo ? '✅ Cliente Creado' : 'ℹ️ Cliente Existente'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                esNuevo 
                    ? 'El cliente se ha creado exitosamente.'
                    : 'Este cliente ya existe en el sistema.',
                style: TextStyle(fontSize: 16),
              ),
              SizedBox(height: 16),
              Container(
                padding: EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.grey[100],
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'ID: ${cliente['id']}',
                      style: TextStyle(fontWeight: FontWeight.bold),
                    ),
                    Text('Nombre: ${cliente['nombre']}'),
                    if (cliente['telefono'] != null)
                      Text('Teléfono: ${cliente['telefono']}'),
                  ],
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
                Navigator.of(context).pop(cliente); // Regresar con el cliente
              },
              child: Text('OK'),
            ),
          ],
        ),
      );
    } else if (resultado['errors'] != null) {
      // Errores de validación
      String mensajesError = '';
      resultado['errors'].forEach((campo, errores) {
        mensajesError += '• ${errores[0]}\n';
      });
      _mostrarError(mensajesError);
    } else {
      _mostrarError(resultado['message']);
    }
  }
  
  void _mostrarError(String mensaje) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('❌ Error'),
        content: Text(mensaje),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('OK'),
          ),
        ],
      ),
    );
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Crear Cliente'),
        backgroundColor: Colors.blue,
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : Form(
              key: _formKey,
              child: ListView(
                padding: EdgeInsets.all(16),
                children: [
                  // Icono
                  Center(
                    child: Icon(
                      Icons.person_add,
                      size: 80,
                      color: Colors.blue,
                    ),
                  ),
                  
                  SizedBox(height: 24),
                  
                  // Campo nombre
                  TextFormField(
                    controller: _nombreController,
                    decoration: InputDecoration(
                      labelText: 'Nombre del Cliente *',
                      hintText: 'Ej: Juan Pérez',
                      prefixIcon: Icon(Icons.person),
                      border: OutlineInputBorder(),
                    ),
                    textCapitalization: TextCapitalization.words,
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return 'El nombre es requerido';
                      }
                      if (value.trim().length > 255) {
                        return 'El nombre es demasiado largo';
                      }
                      return null;
                    },
                  ),
                  
                  SizedBox(height: 16),
                  
                  // Campo teléfono
                  TextFormField(
                    controller: _telefonoController,
                    decoration: InputDecoration(
                      labelText: 'Teléfono (opcional)',
                      hintText: 'Ej: 555-1234',
                      prefixIcon: Icon(Icons.phone),
                      border: OutlineInputBorder(),
                    ),
                    keyboardType: TextInputType.phone,
                    validator: (value) {
                      if (value != null && value.length > 20) {
                        return 'El teléfono es demasiado largo';
                      }
                      return null;
                    },
                  ),
                  
                  SizedBox(height: 8),
                  
                  // Nota informativa
                  Container(
                    padding: EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.blue[50],
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.blue[200]!),
                    ),
                    child: Row(
                      children: [
                        Icon(Icons.info_outline, color: Colors.blue[700]),
                        SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Si el cliente ya existe, se mostrará su información.',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.blue[900],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  
                  SizedBox(height: 24),
                  
                  // Botón crear
                  ElevatedButton(
                    onPressed: _crearCliente,
                    style: ElevatedButton.styleFrom(
                      padding: EdgeInsets.symmetric(vertical: 16),
                      backgroundColor: Colors.blue,
                    ),
                    child: Text(
                      'Crear Cliente',
                      style: TextStyle(fontSize: 18),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
  
  @override
  void dispose() {
    _nombreController.dispose();
    _telefonoController.dispose();
    super.dispose();
  }
}
```

---

## 🎨 CÓDIGO FLUTTER - WIDGET BUSCAR CLIENTE

```dart
import 'package:flutter/material.dart';

class BuscarClienteScreen extends StatefulWidget {
  @override
  _BuscarClienteScreenState createState() => _BuscarClienteScreenState();
}

class _BuscarClienteScreenState extends State<BuscarClienteScreen> {
  final _busquedaController = TextEditingController();
  List<dynamic> _clientes = [];
  bool _isLoading = false;
  
  @override
  void initState() {
    super.initState();
    _cargarClientes();
  }
  
  Future<void> _cargarClientes({String? busqueda}) async {
    setState(() => _isLoading = true);
    
    final resultado = await ClienteService.listarClientes(
      busqueda: busqueda,
    );
    
    setState(() {
      _isLoading = false;
      if (resultado['success']) {
        _clientes = resultado['data'];
      } else {
        _clientes = [];
      }
    });
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Buscar Cliente'),
        backgroundColor: Colors.blue,
      ),
      body: Column(
        children: [
          // Buscador
          Padding(
            padding: EdgeInsets.all(16),
            child: TextField(
              controller: _busquedaController,
              decoration: InputDecoration(
                labelText: 'Buscar cliente',
                hintText: 'Nombre o teléfono',
                prefixIcon: Icon(Icons.search),
                suffixIcon: _busquedaController.text.isNotEmpty
                    ? IconButton(
                        icon: Icon(Icons.clear),
                        onPressed: () {
                          _busquedaController.clear();
                          _cargarClientes();
                        },
                      )
                    : null,
                border: OutlineInputBorder(),
              ),
              onChanged: (value) {
                if (value.length >= 2 || value.isEmpty) {
                  _cargarClientes(busqueda: value);
                }
              },
            ),
          ),
          
          // Lista de clientes
          Expanded(
            child: _isLoading
                ? Center(child: CircularProgressIndicator())
                : _clientes.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.people_outline,
                              size: 64,
                              color: Colors.grey,
                            ),
                            SizedBox(height: 16),
                            Text(
                              'No se encontraron clientes',
                              style: TextStyle(
                                fontSize: 18,
                                color: Colors.grey,
                              ),
                            ),
                          ],
                        ),
                      )
                    : ListView.builder(
                        itemCount: _clientes.length,
                        itemBuilder: (context, index) {
                          final cliente = _clientes[index];
                          return ListTile(
                            leading: CircleAvatar(
                              child: Text(
                                cliente['nombre'][0].toUpperCase(),
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                            title: Text(cliente['nombre']),
                            subtitle: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                if (cliente['telefono'] != null)
                                  Text('📞 ${cliente['telefono']}'),
                                Text(
                                  'Apartados: ${cliente['apartados_activos']} activos / ${cliente['total_apartados']} total',
                                  style: TextStyle(fontSize: 12),
                                ),
                              ],
                            ),
                            trailing: Icon(Icons.arrow_forward_ios, size: 16),
                            onTap: () {
                              // Seleccionar cliente
                              Navigator.pop(context, cliente);
                            },
                          );
                        },
                      ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          // Ir a crear cliente
          final nuevoCliente = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => CrearClienteScreen(),
            ),
          );
          
          if (nuevoCliente != null) {
            // Recargar lista
            _cargarClientes();
          }
        },
        child: Icon(Icons.add),
        backgroundColor: Colors.blue,
      ),
    );
  }
  
  @override
  void dispose() {
    _busquedaController.dispose();
    super.dispose();
  }
}
```

---

## 🧪 RESULTADOS DE TESTS

### ✅ TEST 1: Crear cliente con teléfono
```json
{
  "success": true,
  "message": "Cliente creado exitosamente",
  "data": {
    "id": 44,
    "nombre": "Juan Pérez",
    "telefono": "555-1234",
    "es_nuevo": true
  }
}
```

### ✅ TEST 2: Crear cliente sin teléfono
```json
{
  "success": true,
  "message": "Cliente creado exitosamente",
  "data": {
    "id": 45,
    "nombre": "María González",
    "telefono": null,
    "es_nuevo": true
  }
}
```

### ✅ TEST 3: Cliente duplicado
```json
{
  "success": true,
  "message": "El cliente ya existe",
  "data": {
    "id": 44,
    "nombre": "Juan Pérez",
    "telefono": "555-1234",
    "es_nuevo": false
  }
}
```

### ✅ TEST 4: Error - Nombre vacío
```json
{
  "success": false,
  "message": "Errores de validación",
  "errors": {
    "nombre": ["El nombre del cliente es requerido"]
  }
}
```

### ✅ TEST 5: Listar clientes
```
Total: 45 clientes
```

### ✅ TEST 6: Buscar por nombre "Juan"
```
Total encontrados: 2
  - Juan Pérez - 555-1234
  - Juanita romero - None
```

### ✅ TEST 7: Buscar por teléfono "555"
```
Total encontrados: 1
  - Juan Pérez - 555-1234
```

---

## 🎯 FLUJO DE USO EN LA APP

### 1️⃣ Crear Apartado - Seleccionar Cliente

```dart
// En la pantalla de crear apartado
ElevatedButton(
  onPressed: () async {
    final clienteSeleccionado = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => BuscarClienteScreen(),
      ),
    );
    
    if (clienteSeleccionado != null) {
      setState(() {
        _clienteId = clienteSeleccionado['id'];
        _clienteNombre = clienteSeleccionado['nombre'];
      });
    }
  },
  child: Text('Seleccionar Cliente'),
)
```

### 2️⃣ Crear Cliente Rápido

```dart
// Botón para crear cliente rápido desde cualquier pantalla
FloatingActionButton(
  onPressed: () async {
    final nuevoCliente = await showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Crear Cliente Rápido'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: nombreController,
              decoration: InputDecoration(labelText: 'Nombre *'),
            ),
            TextField(
              controller: telefonoController,
              decoration: InputDecoration(labelText: 'Teléfono'),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () async {
              final resultado = await ClienteService.crearCliente(
                nombre: nombreController.text,
                telefono: telefonoController.text,
              );
              
              if (resultado['success']) {
                Navigator.pop(context, resultado['data']);
              }
            },
            child: Text('Crear'),
          ),
        ],
      ),
    );
    
    if (nuevoCliente != null) {
      // Usar el nuevo cliente
      print('Cliente creado: ${nuevoCliente['id']}');
    }
  },
  child: Icon(Icons.person_add),
)
```

---

## 📊 RESUMEN

### Rutas Disponibles
- ✅ `POST /api/v1/movil/clientes` - Crear cliente
- ✅ `GET /api/v1/movil/clientes` - Listar todos
- ✅ `GET /api/v1/movil/clientes?busqueda=texto` - Buscar cliente

### Características
- ✅ Detección automática de duplicados
- ✅ Validación de campos
- ✅ Búsqueda por nombre y teléfono
- ✅ Teléfono opcional
- ✅ Contador de apartados
- ✅ Mensajes de error en español
- ✅ Campo `es_nuevo` para saber si fue creado o ya existía

### Estado
🚀 **LISTO PARA PRODUCCIÓN**

### Tests Ejecutados
**8/8 exitosos** ✅
