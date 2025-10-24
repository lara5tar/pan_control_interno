@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<x-page-layout 
    title="Gestión de Usuarios"
    description="Administra los usuarios del sistema"
    button-text="Nuevo Usuario"
    button-icon="fas fa-user-plus"
    :button-route="route('usuarios.create')"
>
    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-stat-card 
            icon="fas fa-users"
            label="Total Usuarios"
            value="0"
            bg-color="bg-blue-100"
            icon-color="text-blue-600"
        />

        <x-stat-card 
            icon="fas fa-user-check"
            label="Activos"
            value="0"
            bg-color="bg-green-100"
            icon-color="text-green-600"
        />

        <x-stat-card 
            icon="fas fa-user-tag"
            label="Vendedores"
            value="0"
            bg-color="bg-purple-100"
            icon-color="text-purple-600"
        />
    </div>

    <!-- Tabla de usuarios -->
    <x-card>
        <div class="text-center py-12">
            <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-500 text-lg mb-4">Módulo de usuarios</p>
            <p class="text-gray-400 text-sm">Los usuarios creados se registrarán en el sistema externo</p>
        </div>
    </x-card>
</x-page-layout>
@endsection
