<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarioController;
// TEMPORAL: Controller de Movimientos comentado
// use App\Http\Controllers\MovimientoController;

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

// TEMPORAL: Rutas de movimientos comentadas
// Route::resource('movimientos', MovimientoController::class);
