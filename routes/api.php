<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubInventarioController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ApartadoController;

// API Routes
Route::prefix('v1')->group(function () {
    // SubInventarios
    Route::get('/subinventarios', [SubInventarioController::class, 'apiIndex']);
    Route::get('/mis-subinventarios/{cod_congregante}', [SubInventarioController::class, 'apiMisSubinventarios']);
    Route::get('/subinventarios/{id}/libros', [SubInventarioController::class, 'apiLibrosSubinventario']);
    Route::get('/mis-libros-disponibles/{cod_congregante}', [SubInventarioController::class, 'apiMisLibrosDisponibles']);
    
    // Búsqueda de libros
    Route::get('/libros', [InventarioController::class, 'apiListarLibros']);
    Route::get('/libros/buscar-codigo/{codigo}', [InventarioController::class, 'apiBuscarPorCodigo']);
    Route::get('/libros/{id}/disponibilidad', [InventarioController::class, 'apiDisponibilidadLibro']);
    
    // Clientes
    Route::get('/clientes', [ClienteController::class, 'apiIndex']);
    
    // Ventas
    Route::post('/ventas', [VentaController::class, 'apiStore']);
    
    // Apartados
    Route::post('/apartados', [ApartadoController::class, 'apiStore']);
});

// API Routes de TESTEO
Route::prefix('v1/test')->group(function () {
    // Listar todos los libros con información de vendibilidad
    Route::get('/todos-los-libros', [SubInventarioController::class, 'apiTestListarTodosLibros']);
});

// API Routes sin versión (para uso interno)
Route::get('/apartados/buscar', [ApartadoController::class, 'apiBuscar']);
Route::get('/clientes/buscar', [ClienteController::class, 'apiBuscar']);

// Ruta para ejecutar migraciones (SOLO PARA DESARROLLO/HOSTING SIN TERMINAL)
Route::get('/run-migrations/{secret_key}', function ($secret_key) {
    // Clave secreta para seguridad - cámbiala por algo único
    if ($secret_key !== 'pan_de_vida_2026_migrations') {
        return response()->json([
            'success' => false,
            'message' => 'Acceso no autorizado'
        ], 403);
    }
    
    try {
        // Ejecutar migraciones
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        
        $output = \Illuminate\Support\Facades\Artisan::output();
        
        return response()->json([
            'success' => true,
            'message' => 'Migraciones ejecutadas correctamente',
            'output' => $output
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al ejecutar migraciones',
            'error' => $e->getMessage()
        ], 500);
    }
});
