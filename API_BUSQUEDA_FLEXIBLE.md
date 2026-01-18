# 🔍 API DE BÚSQUEDA FLEXIBLE - APARTADOS

## ✨ NUEVA FUNCIONALIDAD

Las rutas de búsqueda ahora funcionan **CON y SIN parámetros**, permitiendo mayor flexibilidad en tu app móvil.

---

## 📋 ENDPOINTS CON PARÁMETROS OPCIONALES

### 1. Buscar por Folio (FOLIO OPCIONAL)

```
GET /api/v1/movil/apartados/buscar-folio/{folio?}
```

#### **Uso 1: SIN folio (Lista TODOS los apartados)**

```bash
GET /api/v1/movil/apartados/buscar-folio
```

**Respuesta:**
```json
{
  "success": true,
  "total": 3,
  "data": [
    {
      "id": 3,
      "folio": "AP-2026-0001",
      "cliente": {
        "id": 1,
        "nombre": "Clientes en general sin descuento"
      },
      "monto_total": "500.00",
      "saldo_pendiente": "250.00",
      "estado": "activo"
    },
    {
      "id": 4,
      "folio": "AP-2026-0002",
      "...": "..."
    }
  ]
}
```

#### **Uso 2: CON folio (Busca uno específico)**

```bash
GET /api/v1/movil/apartados/buscar-folio/AP-2026-0001
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "id": 3,
    "folio": "AP-2026-0001",
    "cliente": {
      "id": 1,
      "nombre": "Clientes en general sin descuento"
    },
    "monto_total": "500.00",
    "saldo_pendiente": "250.00",
    "estado": "activo"
  }
}
```

**⚠️ Nota:** Cuando buscas por folio específico, devuelve UN objeto. Sin folio devuelve UN ARRAY.

---

### 2. Buscar por Cliente (NOMBRE OPCIONAL)

```
GET /api/v1/movil/apartados/buscar-cliente?nombre={nombre}
```

#### **Uso 1: SIN nombre (Lista TODOS los clientes con apartados)**

```bash
GET /api/v1/movil/apartados/buscar-cliente
```

**Respuesta:**
```json
{
  "success": true,
  "total_clientes": 3,
  "data": [
    {
      "cliente_id": 1,
      "nombre_cliente": "Clientes en general sin descuento",
      "telefono_cliente": null,
      "apartados": [
        {
          "id": 3,
          "folio": "AP-2026-0001",
          "monto_total": "500.00",
          "saldo_pendiente": "250.00"
        },
        {
          "id": 4,
          "folio": "AP-2026-0002",
          "...": "..."
        }
      ]
    },
    {
      "cliente_id": 5,
      "nombre_cliente": "Juan Pérez",
      "apartados": [...]
    }
  ]
}
```

#### **Uso 2: CON nombre (Filtra por nombre)**

```bash
GET /api/v1/movil/apartados/buscar-cliente?nombre=Juan
```

**Respuesta:**
```json
{
  "success": true,
  "total_clientes": 1,
  "data": [
    {
      "cliente_id": 5,
      "nombre_cliente": "Juan Pérez",
      "telefono_cliente": "555-1234",
      "apartados": [
        {
          "id": 10,
          "folio": "AP-2026-0005",
          "monto_total": "750.00"
        }
      ]
    }
  ]
}
```

---

## 🎯 CASOS DE USO EN TU APP MÓVIL

### Caso 1: Pantalla de Búsqueda de Apartados

```dart
// Usuario puede:
// - Ver todos los apartados (sin escribir nada)
// - Buscar por folio específico

class BuscarApartadosScreen extends StatefulWidget {
  @override
  _BuscarApartadosScreenState createState() => _BuscarApartadosScreenState();
}

class _BuscarApartadosScreenState extends State<BuscarApartadosScreen> {
  final TextEditingController _folioController = TextEditingController();
  List apartados = [];
  bool isLoading = false;

  Future<void> buscarApartados() async {
    setState(() => isLoading = true);
    
    String folio = _folioController.text.trim();
    String url;
    
    if (folio.isEmpty) {
      // SIN folio = listar todos
      url = 'http://10.0.2.2:8003/api/v1/movil/apartados/buscar-folio';
    } else {
      // CON folio = buscar específico
      url = 'http://10.0.2.2:8003/api/v1/movil/apartados/buscar-folio/$folio';
    }
    
    try {
      final response = await http.get(
        Uri.parse(url),
        headers: {'Accept': 'application/json'},
      );
      
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        setState(() {
          if (data['data'] is List) {
            // Sin folio = array
            apartados = data['data'];
          } else {
            // Con folio = objeto único
            apartados = [data['data']];
          }
          isLoading = false;
        });
      }
    } catch (e) {
      print('Error: $e');
      setState(() => isLoading = false);
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Buscar Apartados')),
      body: Column(
        children: [
          Padding(
            padding: EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _folioController,
                    decoration: InputDecoration(
                      labelText: 'Folio (opcional)',
                      hintText: 'Ej: AP-2026-0001',
                    ),
                  ),
                ),
                SizedBox(width: 10),
                ElevatedButton(
                  onPressed: buscarApartados,
                  child: Text('Buscar'),
                ),
              ],
            ),
          ),
          
          // Lista de apartados
          Expanded(
            child: isLoading
                ? Center(child: CircularProgressIndicator())
                : ListView.builder(
                    itemCount: apartados.length,
                    itemBuilder: (context, index) {
                      final apartado = apartados[index];
                      return ListTile(
                        title: Text('${apartado['folio']}'),
                        subtitle: Text('Saldo: \$${apartado['saldo_pendiente']}'),
                        onTap: () {
                          // Navegar a detalles
                        },
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
```

---

### Caso 2: Pantalla de Búsqueda por Cliente

```dart
class BuscarPorClienteScreen extends StatefulWidget {
  @override
  _BuscarPorClienteScreenState createState() => _BuscarPorClienteScreenState();
}

class _BuscarPorClienteScreenState extends State<BuscarPorClienteScreen> {
  final TextEditingController _nombreController = TextEditingController();
  List clientes = [];
  bool isLoading = false;

  Future<void> buscarClientes() async {
    setState(() => isLoading = true);
    
    String nombre = _nombreController.text.trim();
    String url = 'http://10.0.2.2:8003/api/v1/movil/apartados/buscar-cliente';
    
    if (nombre.isNotEmpty) {
      // CON nombre = filtrar
      url += '?nombre=$nombre';
    }
    // SIN nombre = listar todos (no necesitas agregar nada)
    
    try {
      final response = await http.get(
        Uri.parse(url),
        headers: {'Accept': 'application/json'},
      );
      
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        setState(() {
          clientes = data['data'];
          isLoading = false;
        });
      }
    } catch (e) {
      print('Error: $e');
      setState(() => isLoading = false);
    }
  }
  
  @override
  void initState() {
    super.initState();
    // Cargar todos los clientes al iniciar
    buscarClientes();
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Buscar por Cliente')),
      body: Column(
        children: [
          Padding(
            padding: EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _nombreController,
                    decoration: InputDecoration(
                      labelText: 'Nombre del Cliente (opcional)',
                      hintText: 'Ej: Juan',
                    ),
                    onChanged: (value) {
                      // Buscar automáticamente mientras escribe
                      if (value.length > 2 || value.isEmpty) {
                        buscarClientes();
                      }
                    },
                  ),
                ),
                IconButton(
                  icon: Icon(Icons.clear),
                  onPressed: () {
                    _nombreController.clear();
                    buscarClientes(); // Mostrar todos
                  },
                ),
              ],
            ),
          ),
          
          // Lista de clientes con sus apartados
          Expanded(
            child: isLoading
                ? Center(child: CircularProgressIndicator())
                : ListView.builder(
                    itemCount: clientes.length,
                    itemBuilder: (context, index) {
                      final cliente = clientes[index];
                      return ExpansionTile(
                        title: Text(cliente['nombre_cliente']),
                        subtitle: Text(
                          '${cliente['apartados'].length} apartado(s)'
                        ),
                        children: [
                          for (var apartado in cliente['apartados'])
                            ListTile(
                              title: Text('Folio: ${apartado['folio']}'),
                              subtitle: Text(
                                'Saldo: \$${apartado['saldo_pendiente']}'
                              ),
                              onTap: () {
                                // Navegar a abonar
                              },
                            ),
                        ],
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
```

---

## 📊 COMPARACIÓN DE RUTAS

| Ruta | Sin Parámetro | Con Parámetro | Tipo Respuesta |
|------|---------------|---------------|----------------|
| `/apartados` | ✅ Lista todos | ❌ No aplica | Array (siempre) |
| `/apartados/buscar-folio/{folio?}` | ✅ Lista todos | ✅ Busca específico | Array sin folio / Objeto con folio |
| `/apartados/buscar-cliente?nombre=` | ✅ Lista todos los clientes | ✅ Filtra por nombre | Array (siempre) |

---

## 🎨 VENTAJAS DE ESTA IMPLEMENTACIÓN

### 1. **Menos código en tu app**
No necesitas dos funciones diferentes para listar y buscar.

### 2. **Mejor UX**
El usuario puede ver todos los apartados y luego filtrar.

### 3. **Búsqueda progresiva**
Puedes implementar búsqueda en tiempo real:
```dart
TextField(
  onChanged: (value) {
    if (value.isEmpty) {
      buscarTodos();
    } else if (value.length >= 3) {
      buscarEspecifico(value);
    }
  },
)
```

### 4. **Manejo de estados más simple**
```dart
// Una sola función para ambos casos
Future<void> buscar([String? termino]) async {
  String url = baseUrl;
  if (termino != null && termino.isNotEmpty) {
    url += '/$termino';
  }
  // Hacer petición...
}
```

---

## ⚠️ CONSIDERACIONES IMPORTANTES

### 1. **Diferencia en el tipo de respuesta con folio:**

```dart
// SIN folio
if (data['data'] is List) {
  apartados = data['data'];  // ✅ Es array
}

// CON folio
if (data['data'] is Map) {
  apartados = [data['data']];  // ✅ Convertir a array
}
```

### 2. **La ruta de cliente SIEMPRE devuelve array:**

```dart
// Con o sin nombre, siempre es array
List<Cliente> clientes = (data['data'] as List)
    .map((json) => Cliente.fromJson(json))
    .toList();
```

### 3. **Manejo de casos vacíos:**

```json
// Sin resultados
{
  "success": false,
  "message": "No se encontraron apartados",
  // No hay 'data'
}
```

---

## 🧪 PRUEBAS

### Test 1: Buscar folio SIN parámetro
```bash
curl "http://localhost:8003/api/v1/movil/apartados/buscar-folio"
```
**Resultado:** ✅ Array con 3 apartados

### Test 2: Buscar folio CON parámetro
```bash
curl "http://localhost:8003/api/v1/movil/apartados/buscar-folio/AP-2026-0001"
```
**Resultado:** ✅ Objeto único

### Test 3: Buscar cliente SIN parámetro
```bash
curl "http://localhost:8003/api/v1/movil/apartados/buscar-cliente"
```
**Resultado:** ✅ Array con 1 cliente (con sus 3 apartados)

### Test 4: Buscar cliente CON parámetro
```bash
curl "http://localhost:8003/api/v1/movil/apartados/buscar-cliente?nombre=Clientes"
```
**Resultado:** ✅ Array con 1 cliente filtrado

---

## 🎯 RESUMEN

✅ **`/apartados/buscar-folio`** sin folio → Lista todos los apartados (array)  
✅ **`/apartados/buscar-folio/AP-2026-0001`** → Busca uno específico (objeto)  
✅ **`/apartados/buscar-cliente`** sin nombre → Lista todos los clientes (array)  
✅ **`/apartados/buscar-cliente?nombre=Juan`** → Filtra clientes (array)

**Todas las rutas funcionan correctamente! 🎉**
