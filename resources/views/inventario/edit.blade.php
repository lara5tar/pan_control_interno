@extends('layouts.app')

@section('title', 'Editar Libro')

@section('page-title', 'Editar Libro')
@section('page-description', 'Actualiza la información del libro')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-card>
        <x-libro-form 
            :libro="$libro"
            :action="route('inventario.update', $libro->id)"
            method="PUT"
            submitText="Actualizar Libro"
        />
    </x-card>
</div>
@endsection
