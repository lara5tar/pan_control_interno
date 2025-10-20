<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalLibros = Libro::count();
        $stockTotal = Libro::sum('stock');
        $valorInventario = Libro::sum(\DB::raw('precio * stock'));

        return view('dashboard', compact('totalLibros', 'stockTotal', 'valorInventario'));
    }
}
