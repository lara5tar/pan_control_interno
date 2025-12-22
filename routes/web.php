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
use App\Http\Controllers\ApartadoController;
use App\Http\Controllers\AbonoController;

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
    
    // Rutas de apartados
    Route::resource('apartados', ApartadoController::class);
    Route::put('/apartados/{apartado}/liquidar', [ApartadoController::class, 'liquidar'])->name('apartados.liquidar');
    Route::put('/apartados/{apartado}/cancelar', [ApartadoController::class, 'cancelar'])->name('apartados.cancelar');
    
    // Rutas de abonos (dentro del módulo de apartados)
    Route::get('/apartados/{apartado}/abonos/crear', [AbonoController::class, 'create'])->name('apartados.abonos.create');
    Route::post('/apartados/{apartado}/abonos', [AbonoController::class, 'store'])->name('apartados.abonos.store');
    Route::delete('/abonos/{abono}', [AbonoController::class, 'destroy'])->name('apartados.abonos.destroy');
    
});

// Ruta especial para ejecutar migraciones de apartados en hosting
// Acceso: /run-apartados-migration?key=TU_CLAVE_SECRETA
Route::get('/run-apartados-migration', function () {
    $key = request('key');
    
    // Validar clave secreta (cámbiala por una segura)
    if ($key !== 'pan2025secure') {
        abort(403, 'Acceso no autorizado');
    }
    
    try {
        // Ejecutar migraciones específicas de apartados
        $output = [];
        
        // 1. Agregar stock_apartado a libros (si no existe)
        \Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_11_24_020235_add_stock_apartado_to_libros_table.php',
            '--force' => true
        ]);
        $output[] = "1️⃣  add_stock_apartado_to_libros:\n" . \Artisan::output();
        
        // 2. Crear tablas apartados, apartado_detalles y abonos (sistema completo)
        \Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_12_21_062800_create_apartados_sistema_table.php',
            '--force' => true
        ]);
        $output[] = "2️⃣  create_apartados_sistema_table:\n" . \Artisan::output();
        
        // 3. Agregar apartado_id a ventas
        \Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_12_21_062801_add_apartado_id_to_ventas_table.php',
            '--force' => true
        ]);
        $output[] = "3️⃣  add_apartado_id_to_ventas:\n" . \Artisan::output();
        
        return response()->json([
            'success' => true,
            'message' => '✅ Migraciones de apartados ejecutadas correctamente',
            'migrations' => [
                '2025_11_24_020235_add_stock_apartado_to_libros_table.php',
                '2025_12_21_062800_create_apartados_sistema_table.php',
                '2025_12_21_062801_add_apartado_id_to_ventas_table.php'
            ],
            'output' => implode("\n\n", $output),
            'timestamp' => now()->toDateTimeString()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => '❌ Error al ejecutar las migraciones',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile())
        ], 500);
    }
})->name('migration.apartados');

// Ruta para limpiar y recrear tablas de apartados (usar solo si hay conflictos)
// Acceso: /clean-apartados-migration?key=TU_CLAVE_SECRETA
Route::get('/clean-apartados-migration', function () {
    $key = request('key');
    
    // Validar clave secreta
    if ($key !== 'pan2025secure') {
        abort(403, 'Acceso no autorizado');
    }
    
    try {
        $output = [];
        
        // 1. Desactivar verificación de llaves foráneas temporalmente
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $output[] = "✓ Llaves foráneas desactivadas temporalmente";
        
        // 2. Eliminar tablas relacionadas con apartados en orden correcto
        // Usar DROP TABLE directamente para asegurar eliminación
        $tablesToDrop = ['abonos', 'apartado_detalles', 'apartado_libro', 'apartados'];
        foreach ($tablesToDrop as $table) {
            try {
                \DB::statement("DROP TABLE IF EXISTS `$table`");
                $output[] = "✓ Tabla '$table' eliminada con DROP TABLE IF EXISTS";
            } catch (\Exception $e) {
                $output[] = "⚠ Error al eliminar tabla '$table': " . $e->getMessage();
            }
        }
        
        // 3. Eliminar columna stock_apartado de libros si existe
        if (\Schema::hasColumn('libros', 'stock_apartado')) {
            \Schema::table('libros', function($table) {
                $table->dropColumn('stock_apartado');
            });
            $output[] = "✓ Columna 'stock_apartado' eliminada de tabla 'libros'";
        } else {
            $output[] = "• Columna 'stock_apartado' no existe en 'libros'";
        }
        
        // 4. Eliminar columna apartado_id de ventas si existe
        if (\Schema::hasTable('ventas') && \Schema::hasColumn('ventas', 'apartado_id')) {
            \Schema::table('ventas', function($table) {
                // Intentar eliminar llave foránea si existe
                try {
                    $table->dropForeign(['apartado_id']);
                } catch (\Exception $e) {
                    // Ignorar si no existe la llave
                }
                $table->dropColumn('apartado_id');
            });
            $output[] = "✓ Columna 'apartado_id' eliminada de tabla 'ventas'";
        } else {
            $output[] = "• Columna 'apartado_id' no existe en 'ventas'";
        }
        
        // 5. Eliminar registros de migrations para estas tablas
        $deletedRows = \DB::table('migrations')
            ->whereIn('migration', [
                '2025_11_24_020150_create_apartados_table',
                '2025_11_24_020235_add_stock_apartado_to_libros_table',
                '2025_12_21_062800_create_apartados_sistema_table',
                '2025_12_21_062801_add_apartado_id_to_ventas_table',
                '2025_11_25_222721_rename_apartados_to_subinventarios'
            ])
            ->delete();
        $output[] = "✓ $deletedRows registro(s) de migraciones eliminados";
        
        // 6. Reactivar verificación de llaves foráneas
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $output[] = "✓ Llaves foráneas reactivadas";
        
        $output[] = "\n========== EJECUTANDO MIGRACIONES ==========\n";
        
        // 7. Ejecutar migraciones en orden
        \Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_11_24_020150_create_apartados_table.php',
            '--force' => true
        ]);
        $output[] = "1️⃣  create_apartados_table:\n" . \Artisan::output();
        
        \Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_11_24_020235_add_stock_apartado_to_libros_table.php',
            '--force' => true
        ]);
        $output[] = "2️⃣  add_stock_apartado_to_libros:\n" . \Artisan::output();
        
        \Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_12_21_062800_create_apartados_sistema_table.php',
            '--force' => true
        ]);
        $output[] = "3️⃣  create_apartados_sistema_table:\n" . \Artisan::output();
        
        \Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_12_21_062801_add_apartado_id_to_ventas_table.php',
            '--force' => true
        ]);
        $output[] = "4️⃣  add_apartado_id_to_ventas:\n" . \Artisan::output();
        
        return response()->json([
            'success' => true,
            'message' => '✅ Tablas limpiadas y migraciones ejecutadas correctamente',
            'output' => implode("\n", $output),
            'timestamp' => now()->toDateTimeString()
        ]);
        
    } catch (\Exception $e) {
        // Asegurarse de reactivar llaves foráneas en caso de error
        try {
            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $ignore) {}
        
        return response()->json([
            'success' => false,
            'message' => '❌ Error al limpiar y ejecutar las migraciones',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
})->name('migration.apartados.clean');
