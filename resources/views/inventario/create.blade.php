@extends('layouts.app')

@section('title', 'Agregar Libro')

@section('page-title', 'Agregar Nuevo Libro')
@section('page-description', 'Registra un nuevo libro en el inventario')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-card>
        <x-libro-form 
            :action="route('inventario.store')"
            method="POST"
            submitText="Guardar Libro"
        />
    </x-card>
</div>
@endsection
