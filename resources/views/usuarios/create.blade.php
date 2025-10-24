@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')
<x-page-layout 
    title="Crear Nuevo Usuario"
    description="Registra un nuevo usuario en el sistema"
    button-text="Volver a Usuarios"
    button-icon="fas fa-arrow-left"
    :button-route="route('usuarios.index')"
>
    <x-card>
        <form action="{{ route('usuarios.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div>
                    <x-form-input
                        name="nombre"
                        label="Nombre"
                        :value="old('nombre')"
                        :required="true"
                        placeholder="Ej: Juan"
                        icon="fas fa-user"
                        helpText="Nombre del usuario"
                    />
                </div>

                <!-- Apellidos -->
                <div>
                    <x-form-input
                        name="apellidos"
                        label="Apellidos"
                        :value="old('apellidos')"
                        :required="true"
                        placeholder="Ej: Pérez García"
                        icon="fas fa-user"
                        helpText="Apellidos del usuario"
                    />
                </div>

                <!-- Usuario -->
                <div>
                    <label for="usuario" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre de Usuario <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400">
                            <i class="fas fa-user-circle"></i>
                        </span>
                        <input 
                            type="text" 
                            name="usuario" 
                            id="usuario" 
                            value="{{ old('usuario') }}"
                            required
                            placeholder="vendedor1"
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('usuario') border-red-500 @enderror"
                        >
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        <i class="fas fa-info-circle"></i> Nombre de usuario para iniciar sesión
                    </p>
                    @error('usuario')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('password') border-red-500 @enderror"
                        >
                        <button 
                            type="button"
                            id="togglePassword"
                            class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 transition-colors"
                            title="Mostrar/Ocultar contraseña"
                        >
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-sm text-amber-600">
                        <i class="fas fa-exclamation-triangle"></i> La contraseña se guardará sin cifrado
                    </p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Rol -->
                <div class="lg:col-span-2">
                    <label for="rol" class="block text-sm font-medium text-gray-700 mb-2">
                        Rol <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400">
                            <i class="fas fa-user-tag"></i>
                        </span>
                        <select 
                            name="rol" 
                            id="rol" 
                            required
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('rol') border-red-500 @enderror"
                        >
                            <option value="">Selecciona un rol</option>
                            <option value="18" selected>Vendedor</option>
                        </select>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        <i class="fas fa-info-circle"></i> Rol que tendrá el usuario en el sistema
                    </p>
                    @error('rol')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Información adicional -->
                <div class="lg:col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-semibold text-blue-900 mb-2">Proceso de creación</h4>
                            <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
                                <li>Se creará el congregante en el sistema externo</li>
                                <li>Se asignará el rol seleccionado</li>
                                <li>Se crearán las credenciales de acceso</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="lg:col-span-2 flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <x-button type="button" variant="secondary" icon="fas fa-times" onclick="window.location='{{ route('usuarios.index') }}'">
                        Cancelar
                    </x-button>
                    <x-button type="submit" variant="primary" icon="fas fa-save">
                        Crear Usuario
                    </x-button>
                </div>
            </div>
        </form>
    </x-card>
</x-page-layout>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleButton && passwordInput && toggleIcon) {
            toggleButton.addEventListener('click', function() {
                // Cambiar el tipo de input
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                
                // Cambiar el icono
                if (type === 'text') {
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            });
        }
    });
</script>
@endpush
@endsection
