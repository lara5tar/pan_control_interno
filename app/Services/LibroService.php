<?php

namespace App\Services;

use App\Models\Libro;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Servicio para la gestión de libros
 * 
 * Responsabilidades:
 * - Construcción de queries con filtros
 * - Cálculo de estadísticas
 * - Validaciones centralizadas
 */
class LibroService
{
    protected $codeGenerator;

    public function __construct(CodeGeneratorService $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }
    
    // ============================================
    // SECCIÓN: CÓDIGOS (Delegación a CodeGeneratorService)
    // ============================================
    
    /**
     * Genera un código de barras único
     */
    public function generateBarcode(): string
    {
        return $this->codeGenerator->generateBarcode();
    }

    /**
     * Genera un código único con sufijo si es necesario
     */
    public function generateUniqueBarcode(string $baseCode): string
    {
        return $this->codeGenerator->generateUniqueBarcode($baseCode);
    }

    /**
     * Genera un código basado en número de fila (para importaciones)
     */
    public function generateBarcodeForRow(int $rowNumber): string
    {
        return $this->codeGenerator->generateBarcodeForRow($rowNumber);
    }

    /**
     * Genera código QR en formato SVG con información del libro
     */
    public function generateQrSvg(string $codigoBarras, string $nombreLibro): string
    {
        return $this->codeGenerator->generateQrSvg($codigoBarras, $nombreLibro);
    }

    // ============================================
    // SECCIÓN: QUERIES Y FILTROS
    // ============================================
    
    /**
     * Construye la query con todos los filtros aplicados
     */
    public function buildFilteredQuery(Request $request): Builder
    {
        $query = Libro::query();

        $this->applySearchFilter($query, $request);
        $this->applyStockFilter($query, $request);
        $this->applyPriceFilter($query, $request);
        $this->applySorting($query, $request);

        return $query;
    }

    /**
     * Calcula estadísticas sobre la query filtrada
     */
    public function calculateStatistics(Builder $query): array
    {
        return [
            'totalLibros' => (clone $query)->count(),
            'stockTotal' => (clone $query)->sum('stock'),
            'valorTotal' => (clone $query)->get()->sum(function($libro) {
                return $libro->stock * $libro->precio;
            }),
        ];
    }

    /**
     * Aplica filtro de búsqueda por nombre o código de barras
     */
    private function applySearchFilter(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_barras', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Aplica filtro por rangos de stock
     */
    private function applyStockFilter(Builder $query, Request $request): void
    {
        if (!$request->filled('stock_filter')) {
            return;
        }

        switch ($request->stock_filter) {
            case '0-100':
                $query->where('stock', '<', 100);
                break;
            case '100-200':
                $query->whereBetween('stock', [100, 200]);
                break;
            case '200-300':
                $query->whereBetween('stock', [200, 300]);
                break;
            case '300-400':
                $query->whereBetween('stock', [300, 400]);
                break;
            case '400-up':
                $query->where('stock', '>=', 400);
                break;
        }
    }

    /**
     * Aplica filtro por rangos de precio
     */
    private function applyPriceFilter(Builder $query, Request $request): void
    {
        if (!$request->filled('precio_filter')) {
            return;
        }

        switch ($request->precio_filter) {
            case '0-100':
                $query->where('precio', '<', 100);
                break;
            case '100-200':
                $query->whereBetween('precio', [100, 200]);
                break;
            case '200-300':
                $query->whereBetween('precio', [200, 300]);
                break;
            case '300-400':
                $query->whereBetween('precio', [300, 400]);
                break;
            case '400-up':
                $query->where('precio', '>=', 400);
                break;
        }
    }

    /**
     * Aplica ordenamiento
     */
    private function applySorting(Builder $query, Request $request): void
    {
        // Si hay filtro de stock, ordenar automáticamente por stock ascendente
        if ($request->filled('stock_filter')) {
            $query->orderBy('stock', 'asc');
            return;
        }

        $ordenar = $request->get('ordenar', 'reciente');
        
        switch ($ordenar) {
            case 'nombre_asc':
                $query->orderBy('nombre', 'asc');
                break;
            case 'nombre_desc':
                $query->orderBy('nombre', 'desc');
                break;
            default: // reciente
                $query->orderBy('id', 'desc');
                break;
        }
    }

    // ============================================
    // SECCIÓN: VALIDACIONES
    // ============================================
    
    /**
     * Reglas de validación para crear libro
     */
    public function getCreateRules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'codigo_barras' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    // Solo verificar unicidad si hay un valor
                    if (!empty($value) && Libro::where('codigo_barras', $value)->exists()) {
                        $fail('Este código de barras ya existe');
                    }
                },
            ],
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ];
    }

    /**
     * Reglas de validación para actualizar libro
     */
    public function getUpdateRules(string $id): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'codigo_barras' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($id) {
                    // Solo verificar unicidad si hay un valor y es diferente al actual
                    if (!empty($value) && Libro::where('codigo_barras', $value)->where('id', '!=', $id)->exists()) {
                        $fail('Este código de barras ya existe');
                    }
                },
            ],
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function getValidationMessages(): array
    {
        return [
            'nombre.required' => 'El nombre del libro es obligatorio',
            'codigo_barras.unique' => 'Este código de barras ya existe',
            'precio.required' => 'El precio es obligatorio',
            'precio.numeric' => 'El precio debe ser un número',
            'precio.min' => 'El precio debe ser mayor o igual a 0',
            'stock.required' => 'El stock es obligatorio',
            'stock.integer' => 'El stock debe ser un número entero',
            'stock.min' => 'El stock debe ser mayor o igual a 0',
        ];
    }
}
