<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarioController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('inventario', InventarioController::class);

// Ruta de ejemplos de JavaScript (solo en desarrollo)
if (app()->environment('local')) {
    Route::get('/ejemplos-js', function () {
        return view('ejemplos-js');
    })->name('ejemplos.js');
}
