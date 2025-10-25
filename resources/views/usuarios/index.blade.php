@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<x-page-layout 
    title="Gestión de Usuarios"
    description="Administra los usuarios del sistema"
    button-text="Nuevo Usuario"
    button-icon="fas fa-user-plus"
    :button-route="route('usuarios.create')"
>
    @if($error)
        <x-alert type="danger" :message="$error" />
    @endif

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-stat-card 
            icon="fas fa-users"
            label="Total Congregantes"
            :value="$totalRegistros"
            bg-color="bg-gray-100"
            icon-color="text-gray-600"
        />

        <x-stat-card 
            icon="fas fa-user-check"
            label="Congregantes Activos"
            :value="$totalActivos"
            bg-color="bg-green-100"
            icon-color="text-green-600"
        />

        <x-stat-card 
            icon="fas fa-user-plus"
            label="Congregantes Nuevos"
            :value="$totalNuevos"
            bg-color="bg-blue-100"
            icon-color="text-blue-600"
        />
    </div>

    <!-- Barra de búsqueda -->
    <x-card>
        <form method="GET" action="{{ route('usuarios.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-4 items-end">
                <!-- Campo de búsqueda -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search text-gray-400"></i> Buscar Congregante
                    </label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Buscar por nombre..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Botones -->
                <div class="flex gap-3">
                    <x-button type="submit" variant="primary" icon="fas fa-search">
                        Buscar
                    </x-button>

                    @if(request('search'))
                        <x-button type="button" variant="secondary" icon="fas fa-times" 
                                  onclick="window.location='{{ route('usuarios.index') }}'">
                            Limpiar
                        </x-button>
                    @endif
                </div>
            </div>

            @if(request('search'))
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle text-blue-500"></i> 
                        Mostrando resultados para: <strong class="text-gray-900">"{{ request('search') }}"</strong>
                    </p>
                </div>
            @endif
        </form>
    </x-card>

    <!-- Tabla de congregantes -->
    <x-card>
        @if(count($congregantes) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Código
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nombre Completo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contacto
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ciudad
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($congregantes as $congregante)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $congregante['CODCONGREGANTE'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ trim($congregante['NOMBRE']) ?: 'Sin nombre' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ trim($congregante['APELLIDOS']) ?: 'Sin apellidos' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(!empty(trim($congregante['CEL'])))
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-phone mr-1"></i> {{ trim($congregante['CEL']) }}
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-400 italic">
                                            Sin celular
                                        </div>
                                    @endif
                                    @if(!empty(trim($congregante['TELCASA'])))
                                        <div class="text-sm text-gray-500">
                                            <i class="fas fa-home mr-1"></i> {{ trim($congregante['TELCASA']) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ !empty(trim($congregante['CIUDAD'])) ? trim($congregante['CIUDAD']) : 'Sin ciudad' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($congregante['CODSTATUS'] == '1')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Nuevo
                                        </span>
                                    @elseif($congregante['CODSTATUS'] == '2')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Activo
                                        </span>
                                    @elseif($congregante['CODSTATUS'] == '3')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Inactivo
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $congregante['CODSTATUS'] ?: 'Sin status' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if(!empty(trim($congregante['MAIL'])))
                                        <span class="text-gray-500">{{ trim($congregante['MAIL']) }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Sin email</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($totalPaginas > 1)
                <div class="mt-4 px-6 py-4 border-t border-gray-200">
                    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
                        <div class="flex justify-between flex-1 sm:hidden">
                            @if($paginaActual > 0)
                                <a href="{{ route('usuarios.index', array_merge(request()->query(), ['pagina' => $paginaActual - 1])) }}" 
                                   class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                    Anterior
                                </a>
                            @else
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                                    Anterior
                                </span>
                            @endif

                            @if($paginaActual < $totalPaginas - 1)
                                <a href="{{ route('usuarios.index', array_merge(request()->query(), ['pagina' => $paginaActual + 1])) }}" 
                                   class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                    Siguiente
                                </a>
                            @else
                                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                                    Siguiente
                                </span>
                            @endif
                        </div>

                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700 leading-5">
                                    Mostrando página
                                    <span class="font-medium">{{ $paginaActual + 1 }}</span>
                                    de
                                    <span class="font-medium">{{ $totalPaginas }}</span>
                                    -
                                    <span class="font-medium">{{ $totalRegistros }}</span>
                                    congregantes en total
                                </p>
                            </div>

                            <div>
                                <span class="relative z-0 inline-flex shadow-sm rounded-md">
                                    {{-- Botón Anterior --}}
                                    @if($paginaActual > 0)
                                        <a href="{{ route('usuarios.index', array_merge(request()->query(), ['pagina' => $paginaActual - 1])) }}" 
                                           rel="prev" 
                                           class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" 
                                           aria-label="Anterior">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    @else
                                        <span aria-disabled="true" aria-label="Anterior">
                                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-default rounded-l-md leading-5" aria-hidden="true">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </span>
                                    @endif

                                    {{-- Números de página --}}
                                    @php
                                        $start = max(0, $paginaActual - 2);
                                        $end = min($totalPaginas - 1, $paginaActual + 2);
                                    @endphp

                                    @if($start > 0)
                                        <a href="{{ route('usuarios.index', array_merge(request()->query(), ['pagina' => 0])) }}" 
                                           class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                            1
                                        </a>
                                        @if($start > 1)
                                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5">...</span>
                                        @endif
                                    @endif

                                    @for($i = $start; $i <= $end; $i++)
                                        @if($i == $paginaActual)
                                            <span aria-current="page">
                                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-gray-100 border border-gray-300 cursor-default leading-5">{{ $i + 1 }}</span>
                                            </span>
                                        @else
                                            <a href="{{ route('usuarios.index', array_merge(request()->query(), ['pagina' => $i])) }}" 
                                               class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" 
                                               aria-label="Ir a página {{ $i + 1 }}">
                                                {{ $i + 1 }}
                                            </a>
                                        @endif
                                    @endfor

                                    @if($end < $totalPaginas - 1)
                                        @if($end < $totalPaginas - 2)
                                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5">...</span>
                                        @endif
                                        <a href="{{ route('usuarios.index', array_merge(request()->query(), ['pagina' => $totalPaginas - 1])) }}" 
                                           class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                            {{ $totalPaginas }}
                                        </a>
                                    @endif

                                    {{-- Botón Siguiente --}}
                                    @if($paginaActual < $totalPaginas - 1)
                                        <a href="{{ route('usuarios.index', array_merge(request()->query(), ['pagina' => $paginaActual + 1])) }}" 
                                           rel="next" 
                                           class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" 
                                           aria-label="Siguiente">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    @else
                                        <span aria-disabled="true" aria-label="Siguiente">
                                            <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-default rounded-r-md leading-5" aria-hidden="true">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </nav>
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg mb-4">No se encontraron congregantes</p>
                <p class="text-gray-400 text-sm">Intenta con otra página o verifica la conexión con la API</p>
            </div>
        @endif
    </x-card>
</x-page-layout>
@endsection
