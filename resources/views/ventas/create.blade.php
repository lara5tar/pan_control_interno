@extends('layouts.app')

@section('title', 'Nueva Venta')

@section('page-title', 'Nueva Venta')
@section('page-description', 'Registra una nueva venta')

@section('content')
<x-page-layout 
    title="Registrar Nueva Venta"
    description="Selecciona los libros y cantidades para la venta"
    button-text="Volver a Ventas"
    button-icon="fas fa-arrow-left"
    :button-route="route('ventas.index')"
>
    <x-card>
        <x-venta-form 
            :action="route('ventas.store')"
            method="POST"
            :libros="$libros"
            submitText="Guardar Venta"
        />
    </x-card>
</x-page-layout>
@endsection
