<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\Venta;
use App\Services\ExcelReportService;
use App\Services\PdfReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EnvioController extends Controller
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

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Envio::with(['ventas.cliente', 'ventas.movimientos']);

        // Filtro por rango de fechas
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_envio', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_envio', '<=', $request->fecha_hasta);
        }

        // Filtro por estado de pago
        if ($request->filled('estado_pago')) {
            $query->where('estado_pago', $request->estado_pago);
        }

        // Filtro por rango de montos
        if ($request->filled('monto_min')) {
            $query->where('monto_a_pagar', '>=', $request->monto_min);
        }
        if ($request->filled('monto_max')) {
            $query->where('monto_a_pagar', '<=', $request->monto_max);
        }

        // Filtro por venta específica
        if ($request->filled('venta_id')) {
            $query->conVenta($request->venta_id);
        }

        // Búsqueda general (ID, guía)
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Ordenar
        $ordenar = $request->get('ordenar', 'reciente');
        switch ($ordenar) {
            case 'antiguo':
                $query->orderBy('fecha_envio', 'asc');
                break;
            case 'monto_mayor':
                $query->orderBy('monto_a_pagar', 'desc');
                break;
            case 'monto_menor':
                $query->orderBy('monto_a_pagar', 'asc');
                break;
            case 'guia':
                $query->orderBy('guia', 'asc');
                break;
            default: // reciente
                $query->orderBy('fecha_envio', 'desc');
                break;
        }

        $envios = $query->paginate(15)->withQueryString();

        // Calcular estadísticas para la vista
        $estadisticas = $this->calcularEstadisticas($query);

        // Obtener ventas para el filtro (solo ventas completadas)
        $ventas = Venta::where('estado', 'completada')
                      ->with('cliente')
                      ->orderBy('fecha_venta', 'desc')
                      ->get();

        return view('envios.index', compact('envios', 'estadisticas', 'ventas'));
    }

    /**
     * Calcular estadísticas de los envíos filtrados
     */
    private function calcularEstadisticas($query)
    {
        // Clonar query para no afectar la paginación
        $queryStats = clone $query;
        
        $envios = $queryStats->get();
        
        return [
            'total_envios' => $envios->count(),
            'total_monto' => $envios->sum('monto_a_pagar'),
            'total_ventas_asociadas' => $envios->sum(function($e) { 
                return $e->ventas->count(); 
            }),
            'envios_pagados' => $envios->where('estado_pago', 'pagado')->count(),
            'envios_pendientes' => $envios->where('estado_pago', 'pendiente')->count(),
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener ventas que:
        // 1. Están marcadas como "tiene_envio = true"
        // 2. NO están asignadas a ningún envío todavía
        // 3. NO están canceladas
        $ventas = Venta::where('tiene_envio', true)
                      ->where('estado', '!=', 'cancelada')
                      ->whereDoesntHave('envios')
                      ->with(['cliente', 'movimientos'])
                      ->orderBy('fecha_venta', 'desc')
                      ->get();

        return view('envios.create', compact('ventas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guia' => 'nullable|string|max:255',
            'fecha_envio' => 'required|date',
            'monto_a_pagar' => 'required|numeric|min:0',
            'notas' => 'nullable|string|max:1000',
            'comprobante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            
            // Ventas asociadas
            'ventas' => 'required|array|min:1',
            'ventas.*' => 'exists:ventas,id',
        ], [
            'fecha_envio.required' => 'La fecha de envío es obligatoria',
            'monto_a_pagar.required' => 'El monto a pagar es obligatorio',
            'ventas.required' => 'Debes seleccionar al menos una venta',
            'ventas.min' => 'Debes seleccionar al menos una venta',
            'comprobante.max' => 'El comprobante no puede pesar más de 5MB',
        ]);

        DB::beginTransaction();
        try {
            // Subir comprobante si existe
            $comprobantePath = null;
            if ($request->hasFile('comprobante')) {
                $comprobantePath = $request->file('comprobante')->store('comprobantes/envios', 'public');
            }

            // Crear el envío
            $envio = Envio::create([
                'guia' => $validated['guia'],
                'fecha_envio' => $validated['fecha_envio'],
                'monto_a_pagar' => $validated['monto_a_pagar'],
                'comprobante' => $comprobantePath,
                'notas' => $validated['notas'],
                'estado_pago' => 'pendiente', // Por defecto pendiente
                'usuario' => 'Admin', // Cambiar por auth()->user()->name
            ]);

            // Asociar las ventas al envío
            $envio->ventas()->attach($validated['ventas']);
            
            // NOTA: No es necesario marcar tiene_envio=true porque ya vienen marcadas desde la creación de la venta

            DB::commit();

            return redirect()->route('envios.show', $envio)
                ->with('success', 'Envío registrado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Eliminar el archivo si se subió
            if (isset($comprobantePath) && $comprobantePath) {
                Storage::disk('public')->delete($comprobantePath);
            }
            
            return back()->withErrors(['error' => 'Error al registrar el envío: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Envio $envio)
    {
        $envio->load(['ventas.cliente', 'ventas.movimientos.libro']);
        
        return view('envios.show', compact('envio'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Envio $envio)
    {
        $envio->load('ventas');
        
        // Obtener ventas disponibles:
        // 1. Marcadas como tiene_envio = true
        // 2. NO canceladas
        // 3. Sin envío O las que están en este envío (para poder mantenerlas)
        $ventasDelEnvio = $envio->ventas->pluck('id')->toArray();
        
        $ventas = Venta::where('tiene_envio', true)
                      ->where('estado', '!=', 'cancelada')
                      ->where(function($query) use ($ventasDelEnvio) {
                          $query->whereDoesntHave('envios')
                                ->orWhereIn('id', $ventasDelEnvio);
                      })
                      ->with(['cliente', 'movimientos'])
                      ->orderBy('fecha_venta', 'desc')
                      ->get();

        return view('envios.edit', compact('envio', 'ventas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Envio $envio)
    {
        $validated = $request->validate([
            'guia' => 'nullable|string|max:255',
            'fecha_envio' => 'required|date',
            'monto_a_pagar' => 'required|numeric|min:0',
            'notas' => 'nullable|string|max:1000',
            'comprobante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            
            // Ventas asociadas
            'ventas' => 'required|array|min:1',
            'ventas.*' => 'exists:ventas,id',
        ]);

        DB::beginTransaction();
        try {
            // Subir nuevo comprobante si existe
            if ($request->hasFile('comprobante')) {
                // Eliminar comprobante anterior si existe
                if ($envio->comprobante) {
                    Storage::disk('public')->delete($envio->comprobante);
                }
                $validated['comprobante'] = $request->file('comprobante')->store('comprobantes/envios', 'public');
            }

            // Actualizar el envío
            $envio->update($validated);

            // Obtener ventas anteriores
            $ventasAnteriores = $envio->ventas->pluck('id')->toArray();
            
            // Actualizar las ventas asociadas
            $envio->ventas()->sync($validated['ventas']);
            
            // Desmarcar solo las ventas que fueron REMOVIDAS del envío
            // (las marcamos como sin envío para que puedan ser asignadas a otro)
            $ventasDesasociadas = array_diff($ventasAnteriores, $validated['ventas']);
            if (!empty($ventasDesasociadas)) {
                // NOTA: Solo desmarcamos si realmente queremos que vuelvan a la lista de disponibles
                // En este caso, las mantenemos marcadas porque siguen necesitando envío
                // Venta::whereIn('id', $ventasDesasociadas)->update(['tiene_envio' => false]);
            }
            
            // Las ventas nuevas ya vienen con tiene_envio=true desde su creación

            DB::commit();

            return redirect()->route('envios.show', $envio)
                ->with('success', 'Envío actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el envío: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Envio $envio)
    {
        DB::beginTransaction();
        try {
            // Obtener las ventas asociadas antes de eliminar
            $ventasIds = $envio->ventas->pluck('id')->toArray();
            
            // Eliminar comprobante si existe
            if ($envio->comprobante) {
                Storage::disk('public')->delete($envio->comprobante);
            }

            $envio->delete();
            
            // NOTA: Las ventas conservan tiene_envio=true para que puedan ser asignadas a otro envío
            // Solo eliminamos la asociación con este envío específico
            
            DB::commit();

            return redirect()->route('envios.index')
                ->with('success', 'Envío eliminado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el envío: ' . $e->getMessage());
        }
    }

    /**
     * Exportar envíos filtrados a Excel
     */
    public function exportExcel(Request $request)
    {
        // Construir query con filtros
        $query = $this->buildFilteredQuery($request);
        $envios = $query->get();
        
        // Crear spreadsheet
        $spreadsheet = $this->excelReportService->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $row = $this->excelReportService->setTitle($sheet, 'REPORTE DE ENVÍOS', 'H', 1);
        $row++; // Espacio
        
        // Filtros aplicados
        $filtros = $this->buildFiltersList($request);
        $row = $this->excelReportService->setFilters($sheet, $filtros, $row);
        
        // Estadísticas
        if ($envios->count() > 0) {
            $totalMonto = $envios->sum('monto_a_pagar');
            
            $sheet->setCellValue('A' . $row, 'RESUMEN:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            
            $sheet->setCellValue('A' . $row, 'Total de envíos: ' . $envios->count());
            $row++;
            $sheet->setCellValue('A' . $row, 'Monto total a pagar: $' . number_format($totalMonto, 2));
            $row++;
            $sheet->setCellValue('A' . $row, 'Pendientes: ' . $envios->where('estado', 'pendiente')->count());
            $row++;
            $sheet->setCellValue('A' . $row, 'En tránsito: ' . $envios->where('estado', 'en_transito')->count());
            $row++;
            $sheet->setCellValue('A' . $row, 'Entregados: ' . $envios->where('estado', 'entregado')->count());
            $row += 2; // Espacio
        }
        
        // Encabezados de tabla
        $headers = ['ID', 'Guía', 'Fecha', 'Ventas', 'Monto a Pagar', 'Estado', 'Notas'];
        $row = $this->excelReportService->setTableHeaders($sheet, $headers, $row);
        
        // Datos
        $data = [];
        foreach ($envios as $envio) {
            $data[] = [
                $envio->id,
                $envio->guia ?: 'Sin guía',
                $envio->fecha_envio->format('d/m/Y'),
                $envio->ventas->count() . ' ventas',
                '$' . number_format($envio->monto_a_pagar, 2),
                $envio->getEstadoLabel(),
                $envio->notas ?: '-',
            ];
        }
        
        $lastRow = $this->excelReportService->fillData($sheet, $data, $row);
        
        // Auto ajustar columnas
        $this->excelReportService->autoSizeColumns($sheet, ['A', 'B', 'C', 'D', 'E', 'F', 'G']);
        
        // Descargar
        $filename = $this->excelReportService->generateFilename('reporte_envios');
        $this->excelReportService->download($spreadsheet, $filename);
    }

    /**
     * Exportar envíos filtrados a PDF
     */
    public function exportPdf(Request $request)
    {
        // Construir query con filtros
        $query = $this->buildFilteredQuery($request);
        $envios = $query->get();
        
        // Preparar filtros
        $filtros = $this->buildFiltersList($request);
        
        // Calcular estadísticas
        $estadisticas = [
            'total' => $envios->count(),
            'monto_total' => $envios->sum('monto_a_pagar'),
            'pendientes' => $envios->where('estado', 'pendiente')->count(),
            'en_transito' => $envios->where('estado', 'en_transito')->count(),
            'entregados' => $envios->where('estado', 'entregado')->count(),
            'cancelados' => $envios->where('estado', 'cancelado')->count(),
        ];
        
        // Obtener estilos base
        $styles = $this->pdfReportService->getBaseStyles();
        
        // Generar PDF
        $filename = $this->pdfReportService->generateFilename('reporte_envios');
        
        return $this->pdfReportService->generate(
            'envios.pdf-report',
            compact('envios', 'filtros', 'estadisticas', 'styles'),
            $filename,
            ['orientation' => 'landscape']
        );
    }

    /**
     * Construir query con filtros (helper privado)
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = Envio::with(['ventas.cliente', 'ventas.movimientos']);

        // Filtro por rango de fechas
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_envio', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_envio', '<=', $request->fecha_hasta);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->estado($request->estado);
        }

        // Filtro por venta
        if ($request->filled('venta_id')) {
            $query->conVenta($request->venta_id);
        }

        // Ordenar por fecha más reciente
        $query->orderBy('fecha_envio', 'desc');

        return $query;
    }

    /**
     * Construir lista de filtros aplicados (helper privado)
     */
    private function buildFiltersList(Request $request): array
    {
        $filtros = [];

        if ($request->filled('venta_id')) {
            $venta = Venta::find($request->venta_id);
            if ($venta) {
                $filtros[] = 'Venta: #' . $venta->id;
            }
        }

        if ($request->filled('estado')) {
            $filtros[] = 'Estado: ' . ucfirst($request->estado);
        }

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $filtros[] = 'Período: ' . $request->fecha_desde . ' al ' . $request->fecha_hasta;
        } elseif ($request->filled('fecha_desde')) {
            $filtros[] = 'Desde: ' . $request->fecha_desde;
        } elseif ($request->filled('fecha_hasta')) {
            $filtros[] = 'Hasta: ' . $request->fecha_hasta;
        }

        if (empty($filtros)) {
            $filtros[] = 'Sin filtros aplicados - Mostrando todos los envíos';
        }

        return $filtros;
    }

    /**
     * Mostrar formulario para marcar envío como pagado
     */
    public function mostrarFormularioPago(Envio $envio)
    {
        return view('envios.marcar-pago', compact('envio'));
    }

    /**
     * Marcar envío como pagado
     */
    public function marcarPagado(Request $request, Envio $envio)
    {
        $validated = $request->validate([
            'comprobante_pago' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB máximo
            'fecha_pago' => 'required|date',
            'referencia_pago' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Subir comprobante de pago si existe
            if ($request->hasFile('comprobante_pago')) {
                // Eliminar comprobante anterior si existe
                if ($envio->comprobante_pago) {
                    Storage::disk('public')->delete($envio->comprobante_pago);
                }
                
                $path = $request->file('comprobante_pago')->store('comprobantes_pago', 'public');
                $validated['comprobante_pago'] = $path;
            }

            $envio->update([
                'estado_pago' => 'pagado',
                'comprobante_pago' => $validated['comprobante_pago'] ?? $envio->comprobante_pago,
                'referencia_pago' => $validated['referencia_pago'] ?? null,
                'fecha_pago' => $validated['fecha_pago'],
            ]);

            DB::commit();

            return redirect()
                ->route('envios.show', $envio)
                ->with('success', 'Envío marcado como pagado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Error al marcar el envío como pagado: ' . $e->getMessage());
        }
    }

    /**
     * Marcar envío como pendiente de pago
     */
    public function marcarPendiente(Envio $envio)
    {
        DB::beginTransaction();
        try {
            $envio->update([
                'estado_pago' => 'pendiente',
                'fecha_pago' => null,
            ]);

            DB::commit();

            return redirect()
                ->route('envios.show', $envio)
                ->with('success', 'Envío marcado como pendiente de pago.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Error al actualizar el estado de pago: ' . $e->getMessage());
        }
    }
}
