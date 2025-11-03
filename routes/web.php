<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ClienteController;

// Rutas públicas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas que requieren autenticación
Route::middleware('checkauth')->group(function () {
    
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('inventario', InventarioController::class);

    // Rutas adicionales para importación de Excel
    Route::get('/inventario-import', [InventarioController::class, 'importView'])->name('inventario.import');
    Route::get('/inventario-template', [InventarioController::class, 'downloadTemplate'])->name('inventario.download-template');
    Route::post('/inventario-import-process', [InventarioController::class, 'importProcess'])->name('inventario.import.process');

    // Ruta para generar código de barras aleatorio
    Route::get('/inventario-generate-barcode', [InventarioController::class, 'generateBarcode'])->name('inventario.generate-barcode');

    // Ruta para descargar código QR
    Route::get('/inventario/{id}/qr/download', [InventarioController::class, 'downloadQR'])->name('inventario.qr.download');

    // Rutas para exportar reportes
    Route::get('/inventario-export/excel', [InventarioController::class, 'exportExcel'])->name('inventario.export.excel');
    Route::get('/inventario-export/pdf', [InventarioController::class, 'exportPdf'])->name('inventario.export.pdf');

    // Rutas de Usuarios
    Route::resource('usuarios', UsuarioController::class)->only(['index', 'show', 'edit', 'update']);
    // TEMPORAL: Crear usuario comentado
    // Route::resource('usuarios', UsuarioController::class)->only(['create', 'store']);

    // Rutas de movimientos
    Route::resource('movimientos', MovimientoController::class);
    
    // Rutas de ventas
    Route::resource('ventas', VentaController::class);
    Route::post('/ventas/{venta}/cancelar', [VentaController::class, 'cancelar'])->name('ventas.cancelar');
    
    // Rutas de clientes
    Route::get('/clientes/search', [ClienteController::class, 'search'])->name('clientes.search');
    Route::resource('clientes', ClienteController::class);
    
});
