@extends('layouts.app')

@section('title', 'Nuevo Apartado')

@section('page-title', 'Nuevo Apartado')
@section('page-description', 'Registra un nuevo apartado de libros')

@section('content')
<x-page-layout 
    title="Registrar Nuevo Apartado"
    description="Reserva libros con anticipo para cliente"
    button-text="Volver a Apartados"
    button-icon="fas fa-arrow-left"
    :button-route="route('apartados.index')"
>
    <x-card>
        <x-apartado-form 
            :action="route('apartados.store')"
            method="POST"
            :libros="$libros"
            :clientes="$clientes"
            :subinventarios="$subinventarios"
            submitText="Guardar Apartado"
        />
    </x-card>
</x-page-layout>
@endsection
