@extends('layouts.app')

@section('title', 'Agregar Libro')

@section('page-title', 'Agregar Nuevo Libro')
@section('page-description', 'Registra un nuevo libro en el inventario')

@section('content')
<div class="space-y-6">
    <!-- Encabezado -->
    <x-page-header 
        title="Registrar Nuevo Libro"
        description="Agrega un nuevo libro al inventario"
        button-text="Volver al Inventario"
        button-icon="fas fa-arrow-left"
        :button-route="route('inventario.index')"
    />

    <div class="max-w-2xl mx-auto">
        <x-card>
            <x-libro-form 
                :action="route('inventario.store')"
                method="POST"
                submitText="Guardar Libro"
            />
        </x-card>
    </div>
</div>
@endsection
