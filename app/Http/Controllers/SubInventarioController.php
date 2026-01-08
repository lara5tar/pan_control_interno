<?php

namespace App\Http\Controllers;

use App\Models\SubInventario;
use App\Models\Libro;
use App\Services\ExcelReportService;
use App\Services\PdfReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubInventarioController extends Controller
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
        $query = SubInventario::with('libros');

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por fecha
        if ($request->filled('fecha')) {
            $query->porFecha($request->fecha);
        }

        // Búsqueda por descripción
        if ($request->filled('search')) {
            $query->where('descripcion', 'like', '%' . $request->search . '%');
        }

        // Ordenar
        $ordenar = $request->get('ordenar', 'reciente');
        switch ($ordenar) {
            case 'antiguo':
                $query->orderBy('fecha_subinventario', 'asc');
                break;
            case 'fecha_asc':
                $query->orderBy('fecha_subinventario', 'asc');
                break;
            case 'fecha_desc':
                $query->orderBy('fecha_subinventario', 'desc');
                break;
            default: // reciente
                $query->orderBy('created_at', 'desc');
                break;
        }

        $subinventarios = $query->paginate(15)->withQueryString();

        return view('subinventarios.index', compact('subinventarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Solo mostrar libros con stock disponible
        $libros = Libro::where('stock', '>', 0)
            ->orderBy('nombre')
            ->get();

        return view('subinventarios.create', compact('libros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_subinventario' => 'required|date',
            'descripcion' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
            
            // Libros
            'libros' => 'required|array|min:1',
            'libros.*.libro_id' => 'required|exists:libros,id',
            'libros.*.cantidad' => 'required|integer|min:1',
        ], [
            'fecha_subinventario.required' => 'La fecha del sub-inventario es obligatoria',
            'libros.required' => 'Debes agregar al menos un libro al sub-inventario',
            'libros.min' => 'Debes agregar al menos un libro al sub-inventario',
        ]);

        DB::beginTransaction();
        try {
            // Validar stock de todos los libros primero (ahora stock representa el inventario general directamente)
            foreach ($validated['libros'] as $item) {
                $libro = Libro::findOrFail($item['libro_id']);
                
                if ($libro->stock < $item['cantidad']) {
                    return back()->withErrors([
                        'error' => "Stock en inventario general insuficiente para '{$libro->nombre}'. Stock disponible: {$libro->stock}"
                    ])->withInput();
                }
            }

            // Crear el sub-inventario
            $subinventario = SubInventario::create([
                'fecha_subinventario' => $validated['fecha_subinventario'],
                'descripcion' => $validated['descripcion'],
                'estado' => 'activo',
                'observaciones' => $validated['observaciones'],
                'usuario' => 'Admin', // Cambiar por auth()->user()->name
            ]);

            // Asociar los libros con sus cantidades
            foreach ($validated['libros'] as $item) {
                $subinventario->libros()->attach($item['libro_id'], [
                    'cantidad' => $item['cantidad']
                ]);

                // Restar del inventario general e incrementar contador de subinventarios
                $libro = Libro::findOrFail($item['libro_id']);
                $libro->decrement('stock', $item['cantidad']);
                $libro->increment('stock_subinventario', $item['cantidad']);
            }

            DB::commit();

            return redirect()->route('subinventarios.show', $subinventario)
                ->with('success', 'Sub-inventario creado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear el sub-inventario: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SubInventario $subinventario)
    {
        $subinventario->load('libros');
        
        // Obtener usuarios asignados a este subinventario
        $usuariosAsignados = DB::table('subinventario_user')
            ->where('subinventario_id', $subinventario->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('subinventarios.show', compact('subinventario', 'usuariosAsignados'));
    }

    /**
     * Mostrar vista para gestionar usuarios del subinventario
     */
    public function usuarios(SubInventario $subinventario)
    {
        $usuariosAsignados = DB::table('subinventario_user')
            ->where('subinventario_id', $subinventario->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('subinventarios.usuarios', compact('subinventario', 'usuariosAsignados'));
    }

    /**
     * Asignar congregante al subinventario
     */
    public function assignUser(Request $request, SubInventario $subinventario)
    {
        $validated = $request->validate([
            'cod_congregante' => 'required|string|max:50',
            'nombre_congregante' => 'required|string|max:255',
        ]);

        try {
            // Verificar si el congregante ya está asignado
            $existe = DB::table('subinventario_user')
                ->where('subinventario_id', $subinventario->id)
                ->where('cod_congregante', $validated['cod_congregante'])
                ->exists();

            if ($existe) {
                return redirect()->route('subinventarios.usuarios', $subinventario)
                    ->with('error', 'Este congregante ya está asignado a este subinventario');
            }

            DB::table('subinventario_user')->insert([
                'subinventario_id' => $subinventario->id,
                'cod_congregante' => $validated['cod_congregante'],
                'nombre_congregante' => $validated['nombre_congregante'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('subinventarios.usuarios', $subinventario)
                ->with('success', 'Congregante asignado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al asignar congregante a subinventario', [
                'error' => $e->getMessage(),
                'subinventario_id' => $subinventario->id
            ]);
            return redirect()->route('subinventarios.usuarios', $subinventario)
                ->with('error', 'Error al asignar el congregante: ' . $e->getMessage());
        }
    }

    /**
     * Remover congregante del subinventario
     */
    public function removeUser(Request $request, SubInventario $subinventario)
    {
        $validated = $request->validate([
            'cod_congregante' => 'required|string|max:50',
        ]);

        $deleted = DB::table('subinventario_user')
            ->where('subinventario_id', $subinventario->id)
            ->where('cod_congregante', $validated['cod_congregante'])
            ->delete();

        if ($deleted) {
            return redirect()->route('subinventarios.usuarios', $subinventario)
                ->with('success', 'Congregante removido correctamente');
        }

        return redirect()->route('subinventarios.usuarios', $subinventario)
            ->with('error', 'No se pudo remover el congregante');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubInventario $subinventario)
    {
        // Solo permitir editar sub-inventarios activos
        if ($subinventario->estado !== 'activo') {
            return redirect()->route('subinventarios.index')
                ->with('warning', 'Solo se pueden editar sub-inventarios activos');
        }

        // Cargar la relación de libros si no está cargada
        $subinventario->load('libros');

        // Obtener todos los libros y calcular stock disponible para este subinventario
        $libros = Libro::orderBy('nombre')->get()->map(function($libro) use ($subinventario) {
            // Buscar si este libro ya está en este subinventario
            $cantidadEnEsteSubinv = $subinventario->libros->where('id', $libro->id)->first()?->pivot->cantidad ?? 0;
            
            // Stock disponible para asignar = stock en inventario general + lo que ya tiene este subinventario
            // Esto permite que al editar puedas aumentar/reducir la cantidad
            $libro->stock_disponible_edicion = $libro->stock + $cantidadEnEsteSubinv;
            
            return $libro;
        });

        return view('subinventarios.edit', compact('subinventario', 'libros'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubInventario $subinventario)
    {
        // Solo permitir actualizar sub-inventarios activos
        if ($subinventario->estado !== 'activo') {
            return redirect()->route('subinventarios.index')
                ->with('warning', 'Solo se pueden editar sub-inventarios activos');
        }

        // Cargar la relación de libros para las validaciones
        $subinventario->load('libros');

        $validated = $request->validate([
            'fecha_subinventario' => 'required|date',
            'descripcion' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
            
            // Libros
            'libros' => 'required|array|min:1',
            'libros.*.libro_id' => 'required|exists:libros,id',
            'libros.*.cantidad' => 'required|integer|min:1',
        ]);

        // Validar que no haya libros duplicados
        $libroIds = collect($validated['libros'])->pluck('libro_id');
        if ($libroIds->count() !== $libroIds->unique()->count()) {
            return back()->withErrors([
                'error' => 'No puedes agregar el mismo libro más de una vez. Por favor, verifica la lista.'
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            // Validar stock ANTES de hacer cambios, considerando lo que ya tiene este subinventario
            foreach ($validated['libros'] as $item) {
                $libro = Libro::findOrFail($item['libro_id']);
                
                // Cantidad actual de este libro en este subinventario
                $cantidadActualEnSub = $subinventario->libros->where('id', $item['libro_id'])->first()?->pivot->cantidad ?? 0;
                
                // Stock disponible = stock en inventario general + lo que ya tiene este subinventario
                $stockDisponible = $libro->stock + $cantidadActualEnSub;
                
                if ($stockDisponible < $item['cantidad']) {
                    DB::rollBack();
                    return back()->withErrors([
                        'error' => "Stock disponible insuficiente para '{$libro->nombre}'. Stock disponible: {$stockDisponible}"
                    ])->withInput();
                }
            }
            
            // Primero devolver el stock de los libros actuales al inventario general
            foreach ($subinventario->libros as $libro) {
                $libro->increment('stock', $libro->pivot->cantidad);
                $libro->decrement('stock_subinventario', $libro->pivot->cantidad);
            }

            // Actualizar el sub-inventario
            $subinventario->update([
                'fecha_subinventario' => $validated['fecha_subinventario'],
                'descripcion' => $validated['descripcion'],
                'observaciones' => $validated['observaciones'],
            ]);

            // Desasociar libros anteriores
            $subinventario->libros()->detach();

            // Asociar los nuevos libros con sus cantidades
            foreach ($validated['libros'] as $item) {
                $subinventario->libros()->attach($item['libro_id'], [
                    'cantidad' => $item['cantidad']
                ]);

                // Restar del inventario general e incrementar contador de subinventarios
                $libro = Libro::findOrFail($item['libro_id']);
                $libro->decrement('stock', $item['cantidad']);
                $libro->increment('stock_subinventario', $item['cantidad']);
            }

            DB::commit();

            return redirect()->route('subinventarios.show', $subinventario)
                ->with('success', 'Sub-inventario actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el sub-inventario: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubInventario $subinventario)
    {
        // No permitir eliminar sub-inventarios completados
        if ($subinventario->estado === 'completado') {
            return back()->with('error', 'No se pueden eliminar sub-inventarios completados');
        }

        DB::beginTransaction();
        try {
            // Si está activo, devolver el stock al inventario general
            if ($subinventario->estado === 'activo') {
                foreach ($subinventario->libros as $libro) {
                    $libro->increment('stock', $libro->pivot->cantidad);
                    $libro->decrement('stock_subinventario', $libro->pivot->cantidad);
                }
            }

            $subinventario->delete();
            DB::commit();

            return redirect()->route('subinventarios.index')
                ->with('success', 'Sub-inventario eliminado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el sub-inventario: ' . $e->getMessage());
        }
    }

    /**
     * Completar un sub-inventario
     */
    public function completar(SubInventario $subinventario)
    {
        if ($subinventario->estado !== 'activo') {
            return back()->with('warning', 'Este sub-inventario no está activo');
        }

        DB::beginTransaction();
        try {
            // Solo reducir el contador de stock en subinventarios
            // (el stock general ya se restó cuando se creó el subinventario)
            foreach ($subinventario->libros as $libro) {
                $libro->decrement('stock_subinventario', $libro->pivot->cantidad);
            }

            $subinventario->update(['estado' => 'completado']);
            DB::commit();

            return back()->with('success', 'Sub-inventario completado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al completar el sub-inventario: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar un sub-inventario (devolver inventario)
     */
    public function cancelar(SubInventario $subinventario)
    {
        if ($subinventario->estado === 'cancelado') {
            return back()->with('warning', 'Este sub-inventario ya está cancelado');
        }

        DB::beginTransaction();
        try {
            // Si estaba activo, devolver el stock del sub-inventario
            if ($subinventario->estado === 'activo') {
                foreach ($subinventario->libros as $libro) {
                    $libro->decrement('stock_subinventario', $libro->pivot->cantidad); // Cambiaremos este nombre después
                }
            }

            $subinventario->update(['estado' => 'cancelado']);
            DB::commit();

            return back()->with('success', 'Sub-inventario cancelado exitosamente. Stock devuelto al inventario.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cancelar el sub-inventario: ' . $e->getMessage());
        }
    }

    /**
     * Devolver parcialmente libros del sub-inventario
     */
    public function devolverParcial(Request $request, SubInventario $subinventario)
    {
        if ($subinventario->estado !== 'activo') {
            return back()->with('warning', 'Este sub-inventario no está activo');
        }

        $validated = $request->validate([
            'libro_id' => 'required|exists:libros,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $libro = $subinventario->libros()->where('libro_id', $validated['libro_id'])->first();
            
            if (!$libro) {
                return back()->with('error', 'Este libro no está en el sub-inventario');
            }

            if ($validated['cantidad'] > $libro->pivot->cantidad) {
                return back()->with('error', 'La cantidad a devolver es mayor a la cantidad en sub-inventario');
            }

            // Devolver al stock y reducir el sub-inventario
            $libroModel = Libro::findOrFail($validated['libro_id']);
            $libroModel->decrement('stock_subinventario', $validated['cantidad']); // Cambiaremos este nombre después

            // Si se devuelve todo, eliminar la relación
            if ($validated['cantidad'] == $libro->pivot->cantidad) {
                $subinventario->libros()->detach($validated['libro_id']);
            } else {
                // Actualizar la cantidad en la tabla pivote
                $subinventario->libros()->updateExistingPivot($validated['libro_id'], [
                    'cantidad' => $libro->pivot->cantidad - $validated['cantidad']
                ]);
            }

            // Si no quedan libros en el sub-inventario, cancelarlo
            if ($subinventario->libros()->count() == 0) {
                $subinventario->update(['estado' => 'cancelado']);
            }

            DB::commit();

            return back()->with('success', 'Stock devuelto parcialmente exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al devolver el stock: ' . $e->getMessage());
        }
    }

    /**
     * API - Obtener lista de subinventarios
     */
    public function apiIndex(Request $request)
    {
        $query = SubInventario::with('libros:id,nombre,codigo_barras');

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por fecha
        if ($request->filled('fecha')) {
            $query->whereDate('fecha_subinventario', $request->fecha);
        }

        // Búsqueda por descripción
        if ($request->filled('search')) {
            $query->where('descripcion', 'like', '%' . $request->search . '%');
        }

        // Ordenar
        $ordenar = $request->get('ordenar', 'reciente');
        switch ($ordenar) {
            case 'antiguo':
                $query->orderBy('fecha_subinventario', 'asc');
                break;
            case 'fecha_asc':
                $query->orderBy('fecha_subinventario', 'asc');
                break;
            case 'fecha_desc':
                $query->orderBy('fecha_subinventario', 'desc');
                break;
            default: // reciente
                $query->orderBy('created_at', 'desc');
                break;
        }

        $subinventarios = $query->paginate($request->get('per_page', 15));

        return response()->json($subinventarios);
    }

    /**
     * API - Obtener subinventarios asignados a un usuario específico
     */
    public function apiMisSubinventarios(Request $request, $codCongregante)
    {
        // Buscar subinventarios donde el usuario tiene acceso
        $subinventariosIds = DB::table('subinventario_user')
            ->where('cod_congregante', $codCongregante)
            ->pluck('subinventario_id');
        
        if ($subinventariosIds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes subinventarios asignados',
                'data' => []
            ], 404);
        }
        
        // Obtener los subinventarios con sus libros
        $subinventarios = SubInventario::with(['libros' => function($query) {
                $query->select('libros.id', 'libros.nombre', 'libros.codigo_barras', 'libros.precio')
                      ->where('subinventario_libro.cantidad', '>', 0); // Solo libros con stock
            }])
            ->whereIn('id', $subinventariosIds)
            ->where('estado', 'activo') // Solo activos
            ->get()
            ->map(function($subinventario) {
                return [
                    'id' => $subinventario->id,
                    'descripcion' => $subinventario->descripcion,
                    'fecha_subinventario' => $subinventario->fecha_subinventario,
                    'estado' => $subinventario->estado,
                    'total_libros' => $subinventario->libros->count(),
                    'total_unidades' => $subinventario->libros->sum('pivot.cantidad'),
                    'libros' => $subinventario->libros->map(function($libro) {
                        return [
                            'id' => $libro->id,
                            'nombre' => $libro->nombre,
                            'codigo_barras' => $libro->codigo_barras,
                            'precio' => $libro->precio,
                            'cantidad_disponible' => $libro->pivot->cantidad
                        ];
                    })
                ];
            });
        
        return response()->json([
            'success' => true,
            'message' => 'Subinventarios encontrados',
            'data' => $subinventarios
        ], 200);
    }

    /**
     * Exportar sub-inventarios filtrados a Excel
     */
    public function exportExcel(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $subinventarios = $query->get();
        
        // Crear spreadsheet usando el servicio
        $spreadsheet = $this->excelReportService->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $row = $this->excelReportService->setTitle($sheet, 'REPORTE DE SUB-INVENTARIOS', 'F', 1);
        $row++; // Espacio
        
        // Filtros aplicados
        $filtros = $this->buildFiltersList($request);
        $row = $this->excelReportService->setFilters($sheet, $filtros, $row);
        
        // Encabezados de tabla
        $headers = ['ID', 'Fecha Sub-Inventario', 'Descripción', 'Libros', 'Unidades Totales', 'Estado'];
        $row = $this->excelReportService->setTableHeaders($sheet, $headers, $row);
        
        // Datos
        $data = [];
        foreach ($subinventarios as $subinventario) {
            $data[] = [
                $subinventario->id,
                $subinventario->fecha_subinventario->format('d/m/Y'),
                $subinventario->descripcion,
                $subinventario->libros->count(),
                $subinventario->total_unidades,
                $this->getEstadoLabel($subinventario->estado),
            ];
        }
        
        $lastRow = $this->excelReportService->fillData($sheet, $data, $row);
        
        // Auto ajustar columnas
        $this->excelReportService->autoSizeColumns($sheet, ['A', 'B', 'C', 'D', 'E', 'F']);
        
        // Descargar
        $filename = $this->excelReportService->generateFilename('reporte_subinventarios');
        $this->excelReportService->download($spreadsheet, $filename);
    }

    /**
     * Exportar sub-inventarios filtrados a PDF
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $subinventarios = $query->get();
        
        // Preparar filtros usando el helper
        $filtros = $this->buildFiltersList($request);
        
        // Obtener estilos base del servicio
        $styles = $this->pdfReportService->getBaseStyles();
        
        // Generar PDF usando el servicio
        $filename = $this->pdfReportService->generateFilename('reporte_subinventarios');
        
        return $this->pdfReportService->generate(
            'subinventarios.pdf-report',
            compact('subinventarios', 'filtros', 'styles'),
            $filename
        );
    }

    /**
     * Construir query con filtros aplicados
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = SubInventario::with('libros');

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por fecha
        if ($request->filled('fecha')) {
            $query->porFecha($request->fecha);
        }

        // Búsqueda por descripción
        if ($request->filled('search')) {
            $query->where('descripcion', 'like', '%' . $request->search . '%');
        }

        // Ordenar
        $ordenar = $request->get('ordenar', 'reciente');
        switch ($ordenar) {
            case 'antiguo':
                $query->orderBy('fecha_subinventario', 'asc');
                break;
            case 'fecha_asc':
                $query->orderBy('fecha_subinventario', 'asc');
                break;
            case 'fecha_desc':
                $query->orderBy('fecha_subinventario', 'desc');
                break;
            default: // reciente
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query;
    }

    /**
     * Construir lista de filtros aplicados
     */
    private function buildFiltersList(Request $request): array
    {
        $filtros = [];
        
        if ($request->filled('search')) {
            $filtros[] = 'Búsqueda: ' . $request->search;
        }
        
        if ($request->filled('estado')) {
            $estadoLabels = [
                'activo' => 'Estado: Activo',
                'completado' => 'Estado: Completado',
                'cancelado' => 'Estado: Cancelado'
            ];
            $filtros[] = $estadoLabels[$request->estado] ?? 'Estado: ' . $request->estado;
        }
        
        if ($request->filled('fecha')) {
            $filtros[] = 'Fecha: ' . date('d/m/Y', strtotime($request->fecha));
        }
        
        if ($request->filled('ordenar')) {
            $ordenarLabels = [
                'reciente' => 'Orden: Más reciente',
                'antiguo' => 'Orden: Más antiguo',
                'fecha_asc' => 'Orden: Fecha ascendente',
                'fecha_desc' => 'Orden: Fecha descendente'
            ];
            $filtros[] = $ordenarLabels[$request->ordenar] ?? 'Orden: ' . $request->ordenar;
        }
        
        if (empty($filtros)) {
            $filtros[] = 'Sin filtros aplicados';
        }
        
        return $filtros;
    }

    /**
     * Helper para obtener etiqueta de estado
     */
    private function getEstadoLabel($estado): string
    {
        return match($estado) {
            'activo' => 'Activo',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado',
            default => $estado
        };
    }

    /**
     * Buscar congregantes desde la API externa (AJAX)
     */
    public function buscarCongregantes(Request $request)
    {
        $codCongregante = session('codCongregante');
        $termino = $request->get('termino', '');
        
        Log::info('Búsqueda de congregantes iniciada', [
            'termino' => $termino,
            'codCongregante' => $codCongregante ? 'presente' : 'ausente'
        ]);
        
        // Verificar sesión
        if (!$codCongregante) {
            return response()->json([
                'error' => true,
                'message' => 'Sesión no válida. Por favor, inicia sesión nuevamente.'
            ], 401);
        }
        
        if (empty($termino) || strlen($termino) < 2) {
            return response()->json([
                'error' => false,
                'congregantes' => []
            ]);
        }

        try {
            $url = 'https://www.sistemasdevida.com/pan/rest2/index.php/congregante/buscar_paginado';
            $params = [
                'codCongregante' => $codCongregante,
                'termino' => $termino,
                'pagina' => 0
            ];
            
            Log::info('Llamando a API externa', [
                'url' => $url,
                'params' => $params
            ]);
            
            $response = \Illuminate\Support\Facades\Http::timeout(10)->post($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Respuesta de API externa', [
                    'error' => $data['error'] ?? 'no definido',
                    'total_congregantes' => count($data['congregantes'] ?? [])
                ]);
                
                if (!$data['error']) {
                    $congregantes = collect($data['congregantes'] ?? [])
                        ->map(function($congregante) {
                            return [
                                'cod_congregante' => $congregante['CODCONGREGANTE'],
                                'nombre_completo' => trim($congregante['NOMBREF'] ?? $congregante['NOMBRE'] . ' ' . $congregante['APELLIDOS']),
                                'ciudad' => $congregante['CIUDAD'] ?? '',
                                'celular' => $congregante['CEL'] ?? '',
                            ];
                        })
                        ->take(10) // Limitar a 10 resultados
                        ->values();
                    
                    return response()->json([
                        'error' => false,
                        'congregantes' => $congregantes
                    ]);
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => $data['message'] ?? 'Error en la API externa'
                    ], 400);
                }
            }

            Log::error('API externa respondió con error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => true,
                'message' => 'La API externa no respondió correctamente'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Excepción al buscar congregantes', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Error al conectar con el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}

