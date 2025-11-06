# Plantillas Reutilizables de Reportes

## Descripción

Este sistema incluye servicios reutilizables para generar reportes en formato **PDF** y **Excel** con estilos consistentes en todo el sistema.

## Servicios Disponibles

### 1. PdfReportService

**Ubicación:** `app/Services/PdfReportService.php`

Servicio para generar reportes PDF con estilos consistentes.

#### Características:
- Estilos CSS base reutilizables
- Encabezados con formato estándar
- Sección de filtros aplicados
- Pie de página con información del sistema
- Badges de colores (success, danger, warning, info)
- Tablas con diseño responsivo

#### Uso Básico:

```php
use App\Services\PdfReportService;

class MiController extends Controller
{
    protected $pdfReportService;

    public function __construct(PdfReportService $pdfReportService)
    {
        $this->pdfReportService = $pdfReportService;
    }

    public function exportPdf(Request $request)
    {
        // 1. Obtener datos
        $datos = MiModelo::all();
        
        // 2. Preparar filtros
        $filtros = ['Filtro 1', 'Filtro 2'];
        
        // 3. Obtener estilos base
        $styles = $this->pdfReportService->getBaseStyles();
        
        // 4. Generar PDF
        $filename = $this->pdfReportService->generateFilename('mi_reporte');
        
        return $this->pdfReportService->generate(
            'mi-vista.pdf-report',
            compact('datos', 'filtros', 'styles'),
            $filename,
            ['orientation' => 'portrait'] // o 'landscape'
        );
    }
}
```

#### Estructura de Vista Blade para PDF:

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Reporte</title>
    <style>
        {!! $styles !!}
    </style>
</head>
<body>
    <div class="header">
        <h1>TÍTULO DEL REPORTE</h1>
        <p>Generado el {{ date('d/m/Y H:i:s') }}</p>
    </div>

    @if(count($filtros) > 0)
        <div class="filters">
            <h3>Filtros Aplicados:</h3>
            <ul>
                @foreach($filtros as $filtro)
                    <li>{{ $filtro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Columna 1</th>
                <th>Columna 2</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $dato)
                <tr>
                    <td>{{ $dato->campo1 }}</td>
                    <td>{{ $dato->campo2 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total de registros: {{ $datos->count() }}</p>
        <p>Pan de Vida - Sistema de Control Interno</p>
    </div>
</body>
</html>
```

#### Clases CSS Disponibles:

- `.header` - Encabezado del reporte
- `.filters` - Sección de filtros
- `.footer` - Pie de página
- `.badge` - Badge genérico
- `.badge-success` - Badge verde
- `.badge-danger` - Badge rojo
- `.badge-warning` - Badge amarillo
- `.badge-info` - Badge azul
- `.badge-gray` - Badge gris
- `.text-right` - Alinear texto a la derecha
- `.text-center` - Alinear texto al centro
- `.text-left` - Alinear texto a la izquierda
- `.summary` - Sección de resumen

---

### 2. ExcelReportService

**Ubicación:** `app/Services/ExcelReportService.php`

Servicio para generar reportes Excel con formato profesional.

#### Características:
- Encabezados con estilos consistentes
- Formato de celdas (moneda, números)
- Ajuste automático de columnas
- Sección de filtros
- Filas de resumen
- Colores del tema aplicados

#### Uso Básico:

```php
use App\Services\ExcelReportService;

class MiController extends Controller
{
    protected $excelReportService;

    public function __construct(ExcelReportService $excelReportService)
    {
        $this->excelReportService = $excelReportService;
    }

    public function exportExcel(Request $request)
    {
        // 1. Obtener datos
        $datos = MiModelo::all();
        
        // 2. Crear spreadsheet
        $spreadsheet = $this->excelReportService->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 3. Título
        $row = $this->excelReportService->setTitle(
            $sheet, 
            'MI REPORTE', 
            'E',  // Última columna a fusionar
            1     // Fila
        );
        $row++; // Espacio
        
        // 4. Filtros (opcional)
        $filtros = ['Filtro 1', 'Filtro 2'];
        $row = $this->excelReportService->setFilters($sheet, $filtros, $row);
        
        // 5. Encabezados de tabla
        $headers = ['ID', 'Nombre', 'Valor', 'Estado'];
        $row = $this->excelReportService->setTableHeaders($sheet, $headers, $row);
        
        // 6. Datos
        $data = [];
        foreach ($datos as $dato) {
            $data[] = [
                $dato->id,
                $dato->nombre,
                '$' . number_format($dato->valor, 2),
                $dato->estado,
            ];
        }
        
        $lastRow = $this->excelReportService->fillData($sheet, $data, $row);
        
        // 7. Formatear columnas de moneda/números (opcional)
        // $this->excelReportService->formatCurrency($sheet, 'C', $row, $lastRow - 1);
        
        // 8. Auto ajustar columnas
        $this->excelReportService->autoSizeColumns($sheet, ['A', 'B', 'C', 'D']);
        
        // 9. Agregar fila de resumen (opcional)
        // $this->excelReportService->addSummaryRow($sheet, [
        //     'label' => 'Total:',
        //     'value' => $datos->sum('valor')
        // ], $lastRow + 1, 'A', 'C');
        
        // 10. Descargar
        $filename = $this->excelReportService->generateFilename('mi_reporte');
        $this->excelReportService->download($spreadsheet, $filename);
    }
}
```

#### Métodos Disponibles:

- `createSpreadsheet()` - Crea un nuevo spreadsheet
- `setTitle($sheet, $title, $endColumn, $row)` - Establece título
- `setFilters($sheet, $filters, $row)` - Agrega sección de filtros
- `setTableHeaders($sheet, $headers, $row)` - Establece encabezados
- `fillData($sheet, $data, $row)` - Llena datos
- `autoSizeColumns($sheet, $columns)` - Ajusta ancho automático
- `formatCurrency($sheet, $column, $startRow, $endRow)` - Formato moneda
- `formatInteger($sheet, $column, $startRow, $endRow)` - Formato número
- `addSummaryRow($sheet, $data, $row, $labelCol, $valueCol)` - Fila resumen
- `download($spreadsheet, $filename)` - Descarga el archivo
- `generateFilename($prefix)` - Genera nombre con timestamp

---

## Ejemplos de Implementación

### Ejemplo 1: Inventario (Ya implementado)

**Controlador:** `app/Http/Controllers/InventarioController.php`
**Vistas:** 
- PDF: `resources/views/inventario/pdf-report-new.blade.php`
- Ruta Excel: `/inventario-export/excel`
- Ruta PDF: `/inventario-export/pdf`

### Ejemplo 2: Movimientos (Ya implementado)

**Controlador:** `app/Http/Controllers/MovimientoController.php`
**Vistas:**
- PDF: `resources/views/movimientos/pdf-report.blade.php`
- Ruta Excel: `/movimientos-export/excel`
- Ruta PDF: `/movimientos-export/pdf`

---

## Agregar Exportación a un Nuevo Módulo

### Paso 1: Inyectar los Servicios en el Constructor

```php
use App\Services\ExcelReportService;
use App\Services\PdfReportService;

class NuevoController extends Controller
{
    protected $excelReportService;
    protected $pdfReportService;

    public function __construct(
        ExcelReportService $excelReportService,
        PdfReportService $pdfReportService
    ) {
        $this->excelReportService = $excelReportService;
        $this->pdfReportService = $pdfReportService;
    }
}
```

### Paso 2: Crear Métodos de Exportación

```php
public function exportExcel(Request $request)
{
    // Ver ejemplo de uso básico arriba
}

public function exportPdf(Request $request)
{
    // Ver ejemplo de uso básico arriba
}
```

### Paso 3: Agregar Rutas

```php
// En routes/web.php
Route::get('/nuevo-modulo-export/excel', [NuevoController::class, 'exportExcel'])->name('nuevo.export.excel');
Route::get('/nuevo-modulo-export/pdf', [NuevoController::class, 'exportPdf'])->name('nuevo.export.pdf');
```

### Paso 4: Agregar Botones en la Vista

```blade
<x-button 
    type="button" 
    variant="success" 
    icon="fas fa-file-excel"
    onclick="window.location='{{ route('nuevo.export.excel', request()->query()) }}'"
>
    Exportar Excel
</x-button>

<x-button 
    type="button" 
    variant="danger" 
    icon="fas fa-file-pdf"
    onclick="window.location='{{ route('nuevo.export.pdf', request()->query()) }}'"
>
    Exportar PDF
</x-button>
```

---

## Personalización de Estilos

### Colores Disponibles

Los servicios utilizan colores consistentes con el tema del sistema:

- **Primary:** `#1F2937` (gray-800)
- **Secondary:** `#6B7280` (gray-500)
- **Success:** `#10B981` (green-500)
- **Danger:** `#EF4444` (red-500)
- **Warning:** `#F59E0B` (amber-500)
- **Info:** `#3B82F6` (blue-500)

### Agregar Estilos Personalizados

Si necesitas estilos adicionales, puedes extender los estilos base en tu vista:

```blade
<style>
    {!! $styles !!}
    
    /* Estilos personalizados adicionales */
    .mi-clase-custom {
        background-color: #custom;
    }
</style>
```

---

## Notas Importantes

1. **Orientación del PDF:**
   - `portrait` - Vertical (por defecto)
   - `landscape` - Horizontal (para tablas con muchas columnas)

2. **Filtros:**
   - Siempre pasa `request()->query()` para mantener los filtros en las exportaciones
   - Ejemplo: `route('modulo.export.excel', request()->query())`

3. **Performance:**
   - Para grandes volúmenes de datos, considera paginar o limitar los resultados
   - Los PDFs son más lentos de generar que Excel

4. **Formato de Fechas:**
   - Usa `date('d/m/Y H:i:s')` para consistencia
   - Los timestamps se generan automáticamente en los nombres de archivo

---

## Soporte

Para más información o dudas sobre la implementación, consulta los ejemplos en:
- `InventarioController.php`
- `MovimientoController.php`
