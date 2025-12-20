<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use App\Services\CodeGeneratorService;
use App\Services\ExcelService;
use App\Services\ExcelReportService;
use App\Services\PdfReportService;
use App\Services\LibroService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class InventarioController extends Controller
{
    protected $libroService;
    protected $excelService;
    protected $excelReportService;
    protected $pdfReportService;
    protected $codeGenerator;

    public function __construct(
        LibroService $libroService,
        ExcelService $excelService,
        ExcelReportService $excelReportService,
        PdfReportService $pdfReportService,
        CodeGeneratorService $codeGenerator
    ) {
        $this->libroService = $libroService;
        $this->excelService = $excelService;
        $this->excelReportService = $excelReportService;
        $this->pdfReportService = $pdfReportService;
        $this->codeGenerator = $codeGenerator;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->libroService->buildFilteredQuery($request);
        $statistics = $this->libroService->calculateStatistics($query);
        $libros = $query->paginate(10);

        return view('inventario.index', array_merge(
            compact('libros'),
            $statistics
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inventario.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->libroService->getCreateRules(),
            $this->libroService->getValidationMessages()
        );

        Libro::create($validated);

        return redirect()->route('inventario.index')
            ->with('success', 'Libro agregado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $libro = Libro::findOrFail($id);
        return view('inventario.show', compact('libro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $libro = Libro::findOrFail($id);
        return view('inventario.edit', compact('libro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $libro = Libro::findOrFail($id);

        $validated = $request->validate(
            $this->libroService->getUpdateRules($id),
            $this->libroService->getValidationMessages()
        );

        $libro->update($validated);

        return redirect()->route('inventario.show', $libro->id)
            ->with('success', 'Libro actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $libro = Libro::findOrFail($id);
        $libro->delete();

        return redirect()->route('inventario.index')
            ->with('success', 'Libro eliminado exitosamente');
    }

    /**
     * Generar código de barras aleatorio
     */
    public function generateBarcode()
    {
        $codigo = $this->libroService->generateBarcode();
        return response()->json(['codigo' => $codigo]);
    }

    /**
     * Mostrar vista de importación
     */
    public function importView()
    {
        return view('inventario.import');
    }

    /**
     * Descargar plantilla de Excel
     */
    public function downloadTemplate()
    {
        $this->excelService->generateTemplate();
    }

    /**
     * Procesar importación de Excel
     */
    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        try {
            $file = $request->file('file');
            
            $result = $this->excelService->import($file->getPathname(), [
                'skip_errors' => $request->boolean('skip_errors'),
            ]);

            $message = $this->excelService->buildResultMessage($result);

            if (empty($result['errors'])) {
                return redirect()->route('inventario.index')
                    ->with('success', $message ?: 'Importación completada exitosamente');
            } else {
                return redirect()->route('inventario.import')
                    ->with('warning', $message)
                    ->with('errors_list', $result['errors']);
            }

        } catch (\Exception $e) {
            return redirect()->route('inventario.import')
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Descargar código QR como imagen SVG con información del libro
     */
    public function downloadQR($id)
    {
        $libro = Libro::findOrFail($id);
        
        $svgContent = $this->codeGenerator->generateQrSvg(
            $libro->codigo_barras,
            $libro->nombre
        );
        
        $fileName = 'qr-' . $libro->codigo_barras . '.svg';
        
        return response($svgContent)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Exportar libros filtrados a Excel
     */
    public function exportExcel(Request $request)
    {
        $query = $this->libroService->buildFilteredQuery($request);
        $libros = $query->get();
        
        // Crear spreadsheet usando el servicio
        $spreadsheet = $this->excelReportService->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $row = $this->excelReportService->setTitle($sheet, 'REPORTE DE INVENTARIO DE LIBROS', 'E', 1);
        $row++; // Espacio
        
        // Filtros aplicados
        $filtros = $this->buildFiltersList($request);
        $row = $this->excelReportService->setFilters($sheet, $filtros, $row);
        
        // Encabezados de tabla
        $headers = ['ID', 'Nombre', 'Código de Barras', 'Precio', 'Stock'];
        $row = $this->excelReportService->setTableHeaders($sheet, $headers, $row);
        
        // Datos
        $data = [];
        foreach ($libros as $libro) {
            $data[] = [
                $libro->id,
                $libro->nombre,
                $libro->codigo_barras ?? 'Sin código',
                '$' . number_format($libro->precio, 2),
                $libro->stock,
            ];
        }
        
        $lastRow = $this->excelReportService->fillData($sheet, $data, $row);
        
        // Auto ajustar columnas
        $this->excelReportService->autoSizeColumns($sheet, ['A', 'B', 'C', 'D', 'E']);
        
        // Descargar
        $filename = $this->excelReportService->generateFilename('reporte_inventario');
        $this->excelReportService->download($spreadsheet, $filename);
    }

    /**
     * Exportar libros filtrados a PDF
     */
    public function exportPdf(Request $request)
    {
        $query = $this->libroService->buildFilteredQuery($request);
        $libros = $query->get();
        
        // Preparar filtros usando el helper
        $filtros = $this->buildFiltersList($request);
        
        // Obtener estilos base del servicio
        $styles = $this->pdfReportService->getBaseStyles();
        
        // Generar PDF usando el servicio
        $filename = $this->pdfReportService->generateFilename('reporte_inventario');
        
        return $this->pdfReportService->generate(
            'inventario.pdf-report-new',
            compact('libros', 'filtros', 'styles'),
            $filename
        );
    }

    /**
     * Construir lista de filtros aplicados (helper privado)
     */
    private function buildFiltersList(Request $request): array
    {
        $filtros = [];
        
        if ($request->filled('search')) {
            $filtros[] = 'Búsqueda: ' . $request->search;
        }
        
        if ($request->filled('stock_filter')) {
            $stockLabels = [
                '0-100' => 'Stock: Menos de 100',
                '100-200' => 'Stock: 100 a 200',
                '200-300' => 'Stock: 200 a 300',
                '300-400' => 'Stock: 300 a 400',
                '400-up' => 'Stock: 400 o más'
            ];
            $filtros[] = $stockLabels[$request->stock_filter] ?? 'Stock: ' . $request->stock_filter;
        }
        
        if ($request->filled('precio_filter')) {
            $precioLabels = [
                '0-100' => 'Precio: Menos de $100',
                '100-200' => 'Precio: $100 a $200',
                '200-300' => 'Precio: $200 a $300',
                '300-400' => 'Precio: $300 a $400',
                '400-up' => 'Precio: $400 o más'
            ];
            $filtros[] = $precioLabels[$request->precio_filter] ?? 'Precio: ' . $request->precio_filter;
        }
        
        return $filtros;
    }

    /**
     * API - Buscar libro por código de barras o QR
     */
    public function apiBuscarPorCodigo($codigo)
    {
        $libro = Libro::where('codigo_barras', $codigo)->first();

        if (!$libro) {
            return response()->json([
                'success' => false,
                'message' => 'Libro no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $libro->id,
                'nombre' => $libro->nombre,
                'codigo_barras' => $libro->codigo_barras,
                'precio' => $libro->precio,
                'stock' => $libro->stock,  // Stock en inventario general
                'stock_subinventario' => $libro->stock_subinventario,
                'stock_total' => $libro->stock_total,  // Stock total (general + subinventarios)
            ]
        ]);
    }
}

