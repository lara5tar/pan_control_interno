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

// API Routes sin versión (para uso interno)
Route::get('/apartados/buscar', [ApartadoController::class, 'apiBuscar']);
Route::get('/clientes/buscar', [ClienteController::class, 'apiBuscar']);
