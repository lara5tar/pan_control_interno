@extends('layouts.app')

@section('title', 'Gestionar Roles')

@section('page-title', 'Gestionar Roles')
@section('page-description', 'Asigna o remueve roles del congregante')

@section('content')
<x-page-layout 
    title="Gestionar Roles"
    :description="'Congregante: ' . $congregante['NOMBREF']"
    button-text="Volver a Detalles"
    button-icon="fas fa-arrow-left"
    :button-route="route('usuarios.show', $congregante['CODCONGREGANTE'])"
>
    @if(session('error'))
        <x-alert type="danger" :message="session('error')" />
    @endif

    <x-card>
        <!-- Información del Congregante -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900">{{ $congregante['NOMBREF'] }}</h3>
            <p class="text-sm text-gray-600 mt-1">
                Código: <span class="font-medium">{{ $congregante['CODCONGREGANTE'] }}</span>
            </p>
        </div>

        <form method="POST" action="{{ route('usuarios.update', $congregante['CODCONGREGANTE']) }}">
            @csrf
            @method('PUT')

            <!-- Instrucciones -->
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Solo puedes gestionar los roles relacionados con la librería. Los demás roles son administrados por el sistema principal.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Roles Gestionables -->
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-user-tag text-purple-600 mr-2"></i> 
                    Roles de Librería
                </h4>

                <div class="space-y-3">
                    @foreach($rolesDisponibles as $rol)
                        @php
                            $tieneRol = collect($rolesActuales)->contains('CODROL', $rol['CODROL']);
                        @endphp
                        
                        <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all {{ $tieneRol ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                            <div class="flex items-center h-6">
                                <input 
                                    type="checkbox" 
                                    name="roles[]" 
                                    value="{{ $rol['CODROL'] }}"
                                    {{ $tieneRol ? 'checked' : '' }}
                                    class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500 focus:ring-2 cursor-pointer"
                                >
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-gray-900 text-base">
                                        {{ $rol['ROL'] }}
                                    </span>
                                    @if($tieneRol)
                                        <span class="ml-2 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i> Asignado
                                        </span>
                                    @endif
                                </div>
                                @if($rol['DESCRIPCION_ROL'])
                                    <p class="text-sm text-gray-600 mt-1">{{ $rol['DESCRIPCION_ROL'] }}</p>
                                @endif
                                <p class="text-xs text-gray-500 mt-1">Código: {{ $rol['CODROL'] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Roles No Gestionables -->
            @php
                $rolesNoGestionables = collect($rolesActuales)->whereNotIn('CODROL', ['18', '19', '20']);
            @endphp

            @if($rolesNoGestionables->count() > 0)
                <div class="mb-6 pt-6 border-t border-gray-200">
                    <h4 class="text-md font-semibold text-gray-700 mb-4">
                        <i class="fas fa-lock text-gray-400 mr-2"></i> 
                        Otros Roles (Solo lectura)
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($rolesNoGestionables as $rol)
                            <div class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-lock text-gray-500"></i>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-semibold text-gray-900">{{ $rol['ROL'] }}</p>
                                    @if($rol['DESCRIPCION_ROL'])
                                        <p class="text-xs text-gray-600">{{ $rol['DESCRIPCION_ROL'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-3 flex items-start">
                        <i class="fas fa-info-circle mr-1 mt-0.5"></i>
                        <span>Estos roles son administrados por el sistema principal y no pueden ser modificados desde aquí.</span>
                    </p>
                </div>
            @endif

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <x-button 
                    type="button"
                    variant="secondary" 
                    icon="fas fa-times"
                    onclick="window.location='{{ route('usuarios.show', $congregante['CODCONGREGANTE']) }}'"
                >
                    Cancelar
                </x-button>
                
                <x-button 
                    type="submit"
                    variant="primary" 
                    icon="fas fa-save"
                >
                    Guardar Cambios
                </x-button>
            </div>
        </form>
    </x-card>
</x-page-layout>
@endsection
