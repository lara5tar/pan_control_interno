@extends('layouts.app')

@section('title', 'Editar Libro')

@section('page-title', 'Editar Libro')
@section('page-description', 'Actualiza la información del libro')

@section('content')
<x-page-layout 
    title="Editar Libro"
    :description="'Actualiza la información de: ' . $libro->nombre"
    button-text="Volver al Inventario"
    button-icon="fas fa-arrow-left"
    :button-route="route('inventario.index')"
>
    <x-card>
        <x-libro-form 
            :libro="$libro"
            :action="route('inventario.update', $libro->id)"
            method="PUT"
            submitText="Actualizar Libro"
        />
    </x-card>
</x-page-layout>
@endsection
