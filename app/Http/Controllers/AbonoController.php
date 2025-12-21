<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\Apartado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AbonoController extends Controller
{
    /**
     * Mostrar formulario para crear un nuevo abono
     */
    public function create(Apartado $apartado)
    {
        // Verificar que el apartado esté activo
        if ($apartado->estado !== 'activo') {
            return redirect()->route('apartados.show', $apartado)
                ->with('warning', 'Solo se pueden registrar abonos en apartados activos');
        }

        // Verificar que tenga saldo pendiente
        if ($apartado->saldo_pendiente <= 0) {
            return redirect()->route('apartados.show', $apartado)
                ->with('info', 'Este apartado ya está completamente pagado');
        }

        $apartado->load(['cliente', 'abonos']);
        
        return view('apartados.abono', compact('apartado'));
    }

    /**
     * Registrar un nuevo abono
     */
    public function store(Request $request, Apartado $apartado)
    {
        // Verificar que el apartado esté activo
        if ($apartado->estado !== 'activo') {
            return redirect()->route('apartados.show', $apartado)
                ->with('error', 'Solo se pueden registrar abonos en apartados activos');
        }

        $validated = $request->validate([
            'fecha_abono' => 'required|date',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:efectivo,transferencia,tarjeta',
            'comprobante' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ], [
            'fecha_abono.required' => 'La fecha del abono es obligatoria',
            'monto.required' => 'El monto es obligatorio',
            'monto.min' => 'El monto debe ser mayor a 0',
            'metodo_pago.required' => 'El método de pago es obligatorio',
        ]);

        DB::beginTransaction();
        try {
            // Verificar que el abono no exceda el saldo
            if ($validated['monto'] > $apartado->saldo_pendiente) {
                return back()->withErrors([
                    'error' => 'El monto excede el saldo pendiente. Máximo: $' . number_format($apartado->saldo_pendiente, 2)
                ])->withInput();
            }

            $saldoAnterior = $apartado->saldo_pendiente;
            $saldoNuevo = $saldoAnterior - $validated['monto'];

            // Crear el abono
            $abono = Abono::create([
                'apartado_id' => $apartado->id,
                'fecha_abono' => $validated['fecha_abono'],
                'monto' => $validated['monto'],
                'saldo_anterior' => $saldoAnterior,
                'saldo_nuevo' => $saldoNuevo,
                'metodo_pago' => $validated['metodo_pago'],
                'comprobante' => $validated['comprobante'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'usuario' => Auth::user()->name ?? 'Sistema',
            ]);

            // Actualizar saldo del apartado
            $apartado->actualizarSaldo();

            DB::commit();

            $apartadoActualizado = $apartado->fresh();

            // Si se pagó completamente, mostrar mensaje especial
            if ($apartadoActualizado->saldo_pendiente <= 0) {
                return redirect()->route('apartados.show', $apartado)
                    ->with('success', '¡Abono registrado exitosamente! El apartado ha sido completamente pagado. Puede proceder a liquidarlo.');
            }

            // Si aún hay saldo, volver al formulario de abonos
            return redirect()->route('apartados.abonos.create', $apartado)
                ->with('success', 'Abono registrado exitosamente. Saldo pendiente: $' . number_format($apartadoActualizado->saldo_pendiente, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el abono: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Eliminar un abono
     */
    public function destroy(Abono $abono)
    {
        $apartado = $abono->apartado;

        // Verificar que el apartado esté activo
        if ($apartado->estado !== 'activo') {
            return back()->with('error', 'No se pueden eliminar abonos de apartados liquidados o cancelados');
        }

        // Solo permitir eliminar el último abono
        $ultimoAbono = $apartado->abonos()->latest()->first();
        if ($abono->id !== $ultimoAbono->id) {
            return back()->with('error', 'Solo se puede eliminar el último abono registrado');
        }

        DB::beginTransaction();
        try {
            // Eliminar el abono
            $abono->delete();

            // Recalcular saldo del apartado
            $apartado->actualizarSaldo();

            DB::commit();

            return back()->with('success', 'Abono eliminado exitosamente. Saldo actualizado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar el abono: ' . $e->getMessage()]);
        }
    }
}
