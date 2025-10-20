@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')
@section('page-description', 'Resumen general del sistema')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Card de estadísticas -->
    <x-card>
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-primary-100 text-primary-600">
                <i class="fas fa-book text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-600 text-sm">Total Libros</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalLibros }}</p>
            </div>
        </div>
    </x-card>

    <x-card>
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-dollar-sign text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-600 text-sm">Valor Inventario</p>
                <p class="text-2xl font-bold text-gray-800">${{ number_format($valorInventario, 2) }}</p>
            </div>
        </div>
    </x-card>

    <x-card>
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-boxes text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-gray-600 text-sm">Stock Total</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stockTotal }}</p>
            </div>
        </div>
    </x-card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-card title="Acceso Rápido">
        <div class="space-y-2">
            <a href="{{ route('inventario.index') }}" class="block p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-boxes text-primary-600 mr-2"></i>
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
@endsection
