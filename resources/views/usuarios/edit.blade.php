@extends('layouts.app')

@section('title', 'Gestionar Roles')

@section('content')
<x-page-layout 
    title="Gestionar Roles"
    :description="'Congregante: ' . $congregante['NOMBREF']"
>
    <x-slot name="header">
        <x-button 
            variant="secondary" 
            icon="fas fa-arrow-left"
            onclick="window.location='{{ route('usuarios.show', $congregante['CODCONGREGANTE']) }}'"
        >
            Volver
        </x-button>
    </x-slot>

    @if(session('error'))
        <x-alert type="danger" :message="session('error')" />
    @endif

    <!-- Información del Congregante -->
    <x-card>
        <div class="flex items-center space-x-4 mb-6 pb-6 border-b">
            <div class="flex-shrink-0">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                    {{ strtoupper(substr($congregante['NOMBRE'], 0, 1)) }}{{ strtoupper(substr($congregante['APELLIDOS'], 0, 1)) }}
                </div>
            </div>
            <div>
                <h3 class="text-xl font-semibold text-gray-900">{{ $congregante['NOMBREF'] }}</h3>
                <p class="text-sm text-gray-500">Código: {{ $congregante['CODCONGREGANTE'] }}</p>
                <p class="text-sm text-gray-500">{{ $congregante['CIUDAD'] ?: 'Sin ciudad' }}</p>
            </div>
        </div>

        <!-- Formulario de Roles -->
        <form method="POST" action="{{ route('usuarios.update', $congregante['CODCONGREGANTE']) }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-user-tag text-purple-600 mr-2"></i> 
                    Selecciona los roles que deseas asignar
                </h4>

                <p class="text-sm text-gray-600 mb-6">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                    Solo puedes gestionar los roles relacionados con la librería. Los demás roles son administrados por el sistema principal.
                </p>

                <!-- Roles disponibles para gestionar -->
                <div class="space-y-3">
                    @foreach($rolesDisponibles as $rol)
                        @php
                            $tieneRol = collect($rolesActuales)->contains('CODROL', $rol['CODROL']);
                        @endphp
                        
                        <div class="flex items-start p-4 border-2 rounded-lg transition-all {{ $tieneRol ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <div class="flex items-center h-5">
                                <input 
                                    type="checkbox" 
                                    name="roles[]" 
                                    value="{{ $rol['CODROL'] }}"
                                    id="rol_{{ $rol['CODROL'] }}"
                                    {{ $tieneRol ? 'checked' : '' }}
                                    class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500 focus:ring-2 cursor-pointer"
                                >
                            </div>
                            <div class="ml-4 flex-1">
                                <label for="rol_{{ $rol['CODROL'] }}" class="font-medium text-gray-900 cursor-pointer block">
                                    {{ $rol['ROL'] }}
                                    @if($tieneRol)
                                        <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i> Asignado
                                        </span>
                                    @endif
                                </label>
                                @if($rol['DESCRIPCION_ROL'])
                                    <p class="text-sm text-gray-600 mt-1">{{ $rol['DESCRIPCION_ROL'] }}</p>
                                @endif
                                <p class="text-xs text-gray-500 mt-1">Código del rol: {{ $rol['CODROL'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Roles actuales no gestionables -->
                @php
                    $rolesNoGestionables = collect($rolesActuales)->whereNotIn('CODROL', ['18', '19']);
                @endphp

                @if($rolesNoGestionables->count() > 0)
                    <div class="mt-8 pt-6 border-t">
                        <h4 class="text-md font-semibold text-gray-700 mb-4">
                            <i class="fas fa-lock text-gray-400 mr-2"></i> 
                            Otros Roles (No editables)
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($rolesNoGestionables as $rol)
                                <div class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $rol['ROL'] }}</p>
                                        @if($rol['DESCRIPCION_ROL'])
                                            <p class="text-xs text-gray-600">{{ $rol['DESCRIPCION_ROL'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 mt-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Estos roles son administrados por el sistema principal y no pueden ser modificados desde aquí.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Botones de acción -->
            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t">
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

    <!-- Información adicional -->
    <x-card>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Importante</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Solo puedes asignar o quitar los roles de <strong>Vendedor</strong> y <strong>Administrador Librería</strong></li>
                            <li>Los cambios se aplicarán inmediatamente</li>
                            <li>Si quitas todos los roles, el congregante seguirá existiendo en el sistema pero no tendrá permisos en la librería</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </x-card>
</x-page-layout>
@endsection
