<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubInventarioController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\InventarioController;

// API Routes
Route::prefix('v1')->group(function () {
    // SubInventarios
    Route::get('/subinventarios', [SubInventarioController::class, 'apiIndex']);
    
    // Búsqueda de libros
    Route::get('/libros/buscar-codigo/{codigo}', [InventarioController::class, 'apiBuscarPorCodigo']);
    
    // Clientes
    Route::get('/clientes', [ClienteController::class, 'apiIndex']);
    
    // Ventas
    Route::post('/ventas', [VentaController::class, 'apiStore']);
});
