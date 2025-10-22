<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use App\Services\CodeGeneratorService;
use App\Services\ExcelService;
use App\Services\LibroService;
use Illuminate\Http\Request;

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
}
