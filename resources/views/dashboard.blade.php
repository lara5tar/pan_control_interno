@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')
@section('page-description', 'Resumen general del sistema')

@section('content')
<div class="space-y-6">
    <!-- Título de la página -->
    <x-page-header 
        title="Panel de Control"
        description="Resumen general del inventario y movimientos"
    />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-stat-card 
            icon="fas fa-book"
            label="Total Libros"
            :value="$totalLibros"
            bg-color="bg-gray-800"
            icon-color="text-white"
        />

        <x-stat-card 
            icon="fas fa-dollar-sign"
            label="Valor Inventario"
            :value="'$' . number_format($valorInventario, 2)"
            bg-color="bg-green-100"
            icon-color="text-green-600"
        />

        <x-stat-card 
            icon="fas fa-boxes"
            label="Stock Total"
            :value="$stockTotal"
            bg-color="bg-yellow-100"
            icon-color="text-yellow-600"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="Acceso Rápido">
            <div class="space-y-2">
                <a href="{{ route('inventario.index') }}" class="block p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-boxes text-gray-800 mr-2"></i>
                    Ver Inventario de Libros
                </a>
                <a href="{{ route('inventario.create') }}" class="block p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-plus text-green-600 mr-2"></i>
                    Agregar Nuevo Libro
                </a>
            </div>
        </x-card>

        <x-card title="Información del Sistema">
            <div class="space-y-2 text-gray-600">
                <p><i class="fas fa-calendar mr-2"></i> Fecha: {{ date('d/m/Y') }}</p>
                <p><i class="fas fa-clock mr-2"></i> Hora: {{ date('H:i:s') }}</p>
                <p><i class="fas fa-user mr-2"></i> Usuario: Admin</p>
            </div>
        </x-card>
    </div>
</div>
@endsection
