<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MovimientoController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('inventario', InventarioController::class);
Route::resource('movimientos', MovimientoController::class);

// Ruta de ejemplos de JavaScript (solo en desarrollo)
if (app()->environment('local')) {
    Route::get('/ejemplos-js', function () {
        return view('ejemplos-js');
    })->name('ejemplos.js');
}
