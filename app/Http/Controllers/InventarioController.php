<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use App\Services\CodeGeneratorService;
use App\Services\ExcelService;
use App\Services\LibroService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class InventarioController extends Controller
{
    protected $libroService;
    protected $excelService;
    protected $codeGenerator;

    public function __construct(
        LibroService $libroService,
        ExcelService $excelService,
        CodeGeneratorService $codeGenerator
    ) {
        $this->libroService = $libroService;
        $this->excelService = $excelService;
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

        return redirect()->route('inventario.index')
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
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título del reporte
        $sheet->setCellValue('A1', 'REPORTE DE INVENTARIO DE LIBROS');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        // Filtros aplicados
        $row = 3;
        $sheet->setCellValue('A' . $row, 'FILTROS APLICADOS:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        
        if ($request->filled('search')) {
            $sheet->setCellValue('A' . $row, 'Búsqueda: ' . $request->search);
            $row++;
        }
        
        if ($request->filled('stock_filter')) {
            $stockLabels = [
                '0-100' => 'Stock: Menos de 100',
                '100-200' => 'Stock: 100 a 200',
                '200-300' => 'Stock: 200 a 300',
                '300-400' => 'Stock: 300 a 400',
                '400-up' => 'Stock: 400 o más'
            ];
            $sheet->setCellValue('A' . $row, $stockLabels[$request->stock_filter] ?? 'Stock: ' . $request->stock_filter);
            $row++;
        }
        
        if ($request->filled('precio_filter')) {
            $precioLabels = [
                '0-100' => 'Precio: Menos de $100',
                '100-200' => 'Precio: $100 a $200',
                '200-300' => 'Precio: $200 a $300',
                '300-400' => 'Precio: $300 a $400',
                '400-up' => 'Precio: $400 o más'
            ];
            $sheet->setCellValue('A' . $row, $precioLabels[$request->precio_filter] ?? 'Precio: ' . $request->precio_filter);
            $row++;
        }
        
        $row++; // Espacio
        
        // Encabezados de tabla
        $headers = ['ID', 'Nombre', 'Código de Barras', 'Precio', 'Stock'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E5E7EB');
            $col++;
        }
        
        $row++;
        
        // Datos
        foreach ($libros as $libro) {
            $sheet->setCellValue('A' . $row, $libro->id);
            $sheet->setCellValue('B' . $row, $libro->nombre);
            $sheet->setCellValue('C' . $row, $libro->codigo_barras ?? 'Sin código');
            $sheet->setCellValue('D' . $row, '$' . number_format($libro->precio, 2));
            $sheet->setCellValue('E' . $row, $libro->stock);
            $row++;
        }
        
        // Ajustar anchos de columna
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Generar archivo
        $writer = new Xlsx($spreadsheet);
        $fileName = 'reporte_inventario_' . date('Y-m-d_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Exportar libros filtrados a PDF
     */
    public function exportPdf(Request $request)
    {
        $query = $this->libroService->buildFilteredQuery($request);
        $libros = $query->get();
        
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
        
        $pdf = Pdf::loadView('inventario.pdf-report', compact('libros', 'filtros'));
        
        return $pdf->download('reporte_inventario_' . date('Y-m-d_His') . '.pdf');
    }
}
