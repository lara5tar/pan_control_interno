@extends('layouts.app')

@section('title', 'Registrar Abono')

@section('content')
<x-page-layout 
    title="Registrar Abono"
    description="Apartado {{ $apartado->folio }}"
    button-text="Volver al Apartado"
    button-icon="fas fa-arrow-left"
    :button-route="route('apartados.show', $apartado)"
>
    <div class="grid grid-cols-3 gap-6">
        <!-- Formulario de Abono -->
        <div class="col-span-2">
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-6">
                    <i class="fas fa-dollar-sign mr-2"></i> Nuevo Abono
                </h3>

                <form action="{{ route('apartados.abonos.store', $apartado) }}" method="POST">
                    @csrf

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" for="fecha_abono" required>Fecha del Abono</label>
                                <input type="date" name="fecha_abono" id="fecha_abono" class="form-input w-full" 
                                       value="{{ old('fecha_abono', date('Y-m-d')) }}" required>
                                @error('fecha_abono')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" for="monto" required>Monto del Abono</label>
                                <input type="number" step="0.01" name="monto" id="monto" class="form-input w-full" 
                                       value="{{ old('monto') }}" min="0.01" max="{{ $apartado->saldo_pendiente }}" required>
                                <p class="text-xs text-gray-500 mt-1">Máximo: ${{ number_format($apartado->saldo_pendiente, 2) }}</p>
                                @error('monto')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="metodo_pago" required>Método de Pago</label>
                            <select name="metodo_pago" id="metodo_pago" class="form-select w-full" required>
                                <option value="efectivo" {{ old('metodo_pago') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                <option value="transferencia" {{ old('metodo_pago') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                <option value="tarjeta" {{ old('metodo_pago') == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                            </select>
                            @error('metodo_pago')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="comprobante">Comprobante / Referencia</label>
                            <input type="text" name="comprobante" id="comprobante" class="form-input w-full" 
                                   value="{{ old('comprobante') }}" placeholder="Número de transferencia, ticket, etc.">
                            @error('comprobante')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="observaciones">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" rows="3" class="form-textarea w-full" 
                                      placeholder="Notas adicionales del abono">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
                        <a href="{{ route('apartados.show', $apartado) }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Registrar Abono
                        </button>
                    </div>
                </form>
            </x-card>
        </div>

        <!-- Información del Apartado -->
        <div>
            <x-card class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-info-circle mr-2"></i> Información
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Cliente</p>
                        <p class="font-medium">{{ $apartado->cliente->nombre }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Folio</p>
                        <p class="font-medium">{{ $apartado->folio }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Fecha de Apartado</p>
                        <p class="font-medium">{{ $apartado->fecha_apartado->format('d/m/Y') }}</p>
                    </div>
                </div>
            </x-card>

            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-calculator mr-2"></i> Estado de Cuenta
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b">
                        <span class="text-gray-600">Monto Total:</span>
                        <span class="font-bold">${{ number_format($apartado->monto_total, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Pagado:</span>
                        <span class="font-bold text-green-600">${{ number_format($apartado->totalPagado, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b">
                        <span class="text-gray-600 font-semibold">Saldo Pendiente:</span>
                        <span class="text-xl font-bold text-orange-600">${{ number_format($apartado->saldo_pendiente, 2) }}</span>
                    </div>
                    <div class="bg-blue-50 p-3 rounded">
                        <p class="text-sm text-blue-800 mb-2">Porcentaje Pagado:</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $apartado->porcentajePagado }}%</p>
                        <div class="w-full bg-blue-200 rounded-full h-2 mt-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $apartado->porcentajePagado }}%"></div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Historial de Abonos Previos -->
    @if($apartado->abonos->count() > 0)
    <x-card class="mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-history mr-2"></i> Historial de Abonos ({{ $apartado->abonos->count() }})
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Saldo Anterior</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Saldo Nuevo</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($apartado->abonos()->latest()->get() as $abono)
                    <tr>
                        <td class="px-4 py-2 text-sm">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2 text-sm">{{ $abono->fecha_abono->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 text-sm font-bold text-green-600">${{ number_format($abono->monto, 2) }}</td>
                        <td class="px-4 py-2 text-sm">${{ number_format($abono->saldo_anterior, 2) }}</td>
                        <td class="px-4 py-2 text-sm">${{ number_format($abono->saldo_nuevo, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
    @endif
</x-page-layout>
@endsection
