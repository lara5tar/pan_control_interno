<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\EnvioController;
use App\Http\Controllers\SubInventarioController;

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
    
    // Rutas para exportar reportes de movimientos
    Route::get('/movimientos-export/excel', [MovimientoController::class, 'exportExcel'])->name('movimientos.export.excel');
    Route::get('/movimientos-export/pdf', [MovimientoController::class, 'exportPdf'])->name('movimientos.export.pdf');
    
    // Rutas de ventas
    Route::resource('ventas', VentaController::class);
    Route::post('/ventas/{venta}/cancelar', [VentaController::class, 'cancelar'])->name('ventas.cancelar');
    
    // Rutas para exportar reportes de ventas
    Route::get('/ventas-export/excel', [VentaController::class, 'exportExcel'])->name('ventas.export.excel');
    Route::get('/ventas-export/pdf', [VentaController::class, 'exportPdf'])->name('ventas.export.pdf');
    
    // Rutas de clientes
    Route::get('/clientes/search', [ClienteController::class, 'search'])->name('clientes.search');
    Route::resource('clientes', ClienteController::class);
    
    // Rutas de pagos (dentro del módulo de ventas)
    Route::get('/ventas/{venta}/pagos/crear', [PagoController::class, 'create'])->name('ventas.pagos.create');
    Route::post('/ventas/{venta}/pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::delete('/pagos/{pago}', [PagoController::class, 'destroy'])->name('pagos.destroy');
    
    // Rutas de envíos
    Route::resource('envios', EnvioController::class);
    
    // Rutas para marcar estado de pago en envíos
    Route::get('/envios/{envio}/marcar-pago', [EnvioController::class, 'mostrarFormularioPago'])->name('envios.mostrar-pago');
    Route::post('/envios/{envio}/marcar-pagado', [EnvioController::class, 'marcarPagado'])->name('envios.marcar-pagado');
    Route::post('/envios/{envio}/marcar-pendiente', [EnvioController::class, 'marcarPendiente'])->name('envios.marcar-pendiente');
    
    // Rutas para exportar reportes de envíos
    Route::get('/envios-export/excel', [EnvioController::class, 'exportExcel'])->name('envios.export.excel');
    Route::get('/envios-export/pdf', [EnvioController::class, 'exportPdf'])->name('envios.export.pdf');
    
    // Rutas de sub-inventarios
    Route::resource('subinventarios', SubInventarioController::class);
    Route::post('/subinventarios/{subinventario}/completar', [SubInventarioController::class, 'completar'])->name('subinventarios.completar');
    Route::post('/subinventarios/{subinventario}/cancelar', [SubInventarioController::class, 'cancelar'])->name('subinventarios.cancelar');
    Route::post('/subinventarios/{subinventario}/devolver-parcial', [SubInventarioController::class, 'devolverParcial'])->name('subinventarios.devolver-parcial');
    
});
