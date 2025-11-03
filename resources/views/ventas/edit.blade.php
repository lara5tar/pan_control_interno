@extends('layouts.app')

@section('title', 'Editar Venta')

@section('page-title', 'Editar Venta')
@section('page-description', 'Actualiza la información de la venta')

@section('content')
<x-page-layout 
    title="Editar Venta"
    :description="'Actualiza la información de: ' . $venta->codigo"
    button-text="Volver a Ventas"
    button-icon="fas fa-arrow-left"
    :button-route="route('ventas.index')"
>
    <x-card>
        <x-venta-form 
            :venta="$venta"
            :action="route('ventas.update', $venta)"
            method="PUT"
            :libros="$libros"
            submitText="Actualizar Venta"
        />
    </x-card>
</x-page-layout>
@endsection
