@extends('layouts.app')

@section('title', 'Detalle del Congregante')

@section('page-title', 'Detalle del Congregante')
@section('page-description', 'Información completa del congregante')

@section('content')
<x-page-layout 
    title="Detalle del Congregante"
    :description="'Información completa de: ' . $congregante['NOMBREF']"
    button-text="Volver a Usuarios"
    button-icon="fas fa-arrow-left"
    :button-route="route('usuarios.index')"
>
    @if(session('success'))
        <x-alert type="success" :message="session('success')" />
    @endif

    @if(session('error'))
        <x-alert type="danger" :message="session('error')" />
    @endif

    <!-- Grid Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Personal (2 columnas) -->
        <div class="lg:col-span-2">
            <x-card title="Información Personal" class="h-full">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Código</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $congregante['CODCONGREGANTE'] }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Nombre Completo</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $congregante['NOMBREF'] }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Nombre</p>
                        <p class="text-lg font-semibold {{ $congregante['NOMBRE'] ? 'text-gray-800' : 'text-gray-400 italic' }}">
                            {{ $congregante['NOMBRE'] ?: 'No especificado' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Apellidos</p>
                        <p class="text-lg font-semibold {{ $congregante['APELLIDOS'] ? 'text-gray-800' : 'text-gray-400 italic' }}">
                            {{ $congregante['APELLIDOS'] ?: 'No especificado' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Sexo</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $congregante['SEXOF'] ?: 'No especificado' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Estado Civil</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $congregante['EDOCIVF'] ?: 'No especificado' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Fecha de Alta</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $congregante['FECALTAF'] ?: 'No especificado' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Edad</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $congregante['EDAD'] ?: 'No especificado' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Ciudad</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $congregante['CIUDAD'] ?: 'No especificado' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">Estatus</p>
                        <p class="text-lg font-semibold">
                            @if($congregante['CODSTATUS'] == '1')
                                <span class="px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                    {{ $congregante['ESTATUS'] }}
                                </span>
                            @elseif($congregante['CODSTATUS'] == '2')
                                <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-800">
                                    {{ $congregante['ESTATUS'] }}
                                </span>
                            @elseif($congregante['CODSTATUS'] == '3')
                                <span class="px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-800">
                                    {{ $congregante['ESTATUS'] }}
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-800">
                                    {{ $congregante['ESTATUS'] ?: 'Sin estatus' }}
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Información de Contacto (1 columna) -->
        <div class="lg:col-span-1">
            <x-card title="Contacto" class="h-full">
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">
                            <i class="fas fa-mobile-alt mr-1"></i> Celular
                        </p>
                        <p class="text-base font-semibold {{ $congregante['CEL'] ? 'text-gray-800' : 'text-gray-400 italic' }}">
                            {{ $congregante['CEL'] ?: 'No especificado' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">
                            <i class="fas fa-home mr-1"></i> Teléfono Casa
                        </p>
                        <p class="text-base font-semibold {{ $congregante['TELCASA'] ? 'text-gray-800' : 'text-gray-400 italic' }}">
                            {{ $congregante['TELCASA'] ?: 'No especificado' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">
                            <i class="fas fa-envelope mr-1"></i> Email
                        </p>
                        <p class="text-base font-semibold {{ $congregante['MAIL'] ? 'text-gray-800' : 'text-gray-400 italic' }}">
                            {{ $congregante['MAIL'] ?: 'No especificado' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 mb-1">
                            <i class="fas fa-map-marker-alt mr-1"></i> Dirección
                        </p>
                        <p class="text-base font-semibold text-gray-800">
                            {{ $congregante['CALLE'] ?: 'No especificado' }}
                        </p>
                        @if($congregante['COLONIA'])
                            <p class="text-sm text-gray-600 mt-1">{{ $congregante['COLONIA'] }}</p>
                        @endif
                        @if($congregante['CODPOSTAL'])
                            <p class="text-sm text-gray-600">CP: {{ $congregante['CODPOSTAL'] }}</p>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Grid de Roles y Acciones -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Roles Asignados -->
        <x-card title="Roles Asignados">
            @if(count($roles) > 0)
                <div class="space-y-3">
                    @foreach($roles as $rol)
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                            <div class="flex items-center">
                                @if(in_array($rol['CODROL'], ['18', '19']))
                                    <span class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 mr-3">
                                        <i class="fas fa-check"></i>
                                    </span>
                                @else
                                    <span class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 mr-3">
                                        <i class="fas fa-tag"></i>
                                    </span>
                                @endif
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $rol['ROL'] }}</p>
                                    @if($rol['DESCRIPCION_ROL'])
                                        <p class="text-sm text-gray-600">{{ $rol['DESCRIPCION_ROL'] }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">Código: {{ $rol['CODROL'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Nota:</strong> Solo puedes gestionar los roles de <strong>Vendedor</strong> y <strong>Admin Librería</strong>.
                    </p>
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-user-tag text-gray-300 text-5xl mb-3"></i>
                    <p class="text-gray-500 font-medium">Sin roles asignados</p>
                    <p class="text-gray-400 text-sm mt-1">No tiene roles configurados</p>
                </div>
            @endif
        </x-card>

        <!-- Acciones -->
        <x-card title="Acciones">
            <div class="space-y-3">
                <x-button 
                    variant="primary" 
                    icon="fas fa-edit" 
                    onclick="window.location='{{ route('usuarios.edit', $congregante['CODCONGREGANTE']) }}'" 
                    class="w-full justify-center"
                >
                    Gestionar Roles
                </x-button>
                
                <x-button 
                    variant="secondary" 
                    icon="fas fa-arrow-left" 
                    onclick="window.location='{{ route('usuarios.index') }}'" 
                    class="w-full justify-center"
                >
                    Volver al Listado
                </x-button>
            </div>

            @if($congregante['OBSERVACIONES'])
                <div class="mt-6 pt-6 border-t">
                    <p class="text-sm text-gray-600 mb-2">
                        <i class="fas fa-comment text-yellow-600 mr-1"></i>
                        <strong>Observaciones:</strong>
                    </p>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap bg-gray-50 p-3 rounded">{{ $congregante['OBSERVACIONES'] }}</p>
                </div>
            @endif
        </x-card>
    </div>
</x-page-layout>
@endsection
