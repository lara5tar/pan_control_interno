<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\Apartado;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AbonoMovilController extends Controller
{
    /**
     * Buscar apartados por folio
     * GET /api/v1/movil/apartados/buscar-folio/{folio}
     */
    public function buscarPorFolio($folio)
    {
        try {
            $apartado = Apartado::with(['cliente', 'detalles.libro', 'abonos'])
                ->where('folio', $folio)
                ->first();

            if (!$apartado) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró ningún apartado con ese folio'
                ], 404);
            }

            // Verificar si el apartado está en estado activo
            if ($apartado->estado === 'cancelado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este apartado está cancelado y no puede recibir abonos'
                ], 400);
            }

            if ($apartado->estado === 'liquidado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este apartado ya está liquidado'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatearApartado($apartado)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar apartado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar apartados por nombre de cliente
     * GET /api/v1/movil/apartados/buscar-cliente?nombre={nombre}
     */
    public function buscarPorCliente(Request $request)
    {
        try {
            $nombre = $request->query('nombre');

            if (empty($nombre)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe proporcionar el nombre del cliente'
                ], 400);
            }

            // Buscar clientes que coincidan con el nombre
            $clientes = Cliente::where('nombre', 'like', '%' . $nombre . '%')
                ->with(['apartados' => function ($query) {
                    $query->whereIn('estado', ['activo', 'vencido'])
                        ->with(['detalles.libro', 'abonos'])
                        ->orderBy('fecha_apartado', 'desc');
                }])
                ->get();

            if ($clientes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron clientes con ese nombre'
                ], 404);
            }

            // Filtrar clientes que tengan apartados activos
            $clientesConApartados = $clientes->filter(function ($cliente) {
                return $cliente->apartados->isNotEmpty();
            })->values();

            if ($clientesConApartados->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron apartados activos para clientes con ese nombre'
                ], 404);
            }

            // Formatear respuesta
            $resultado = $clientesConApartados->map(function ($cliente) {
                return [
                    'cliente_id' => $cliente->id,
                    'nombre_cliente' => $cliente->nombre,
                    'telefono_cliente' => $cliente->telefono,
                    'apartados' => $cliente->apartados->map(function ($apartado) {
                        return $this->formatearApartado($apartado);
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar apartados por cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar un nuevo abono
     * POST /api/v1/movil/abonos
     */
    public function registrarAbono(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'apartado_id' => 'required|exists:apartados,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:efectivo,transferencia,tarjeta',
            'comprobante' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
            'usuario' => 'required|string|max:100'
        ], [
            'apartado_id.required' => 'El ID del apartado es requerido',
            'apartado_id.exists' => 'El apartado no existe',
            'monto.required' => 'El monto es requerido',
            'monto.numeric' => 'El monto debe ser un número',
            'monto.min' => 'El monto debe ser mayor a 0',
            'metodo_pago.required' => 'El método de pago es requerido',
            'metodo_pago.in' => 'El método de pago debe ser: efectivo, transferencia o tarjeta',
            'usuario.required' => 'El usuario es requerido'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Obtener el apartado
            $apartado = Apartado::findOrFail($request->apartado_id);

            // Verificar estado del apartado
            if ($apartado->estado === 'cancelado') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede abonar a un apartado cancelado'
                ], 400);
            }

            if ($apartado->estado === 'liquidado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este apartado ya está liquidado'
                ], 400);
            }

            // Verificar que el monto no exceda el saldo pendiente
            if ($request->monto > $apartado->saldo_pendiente) {
                return response()->json([
                    'success' => false,
                    'message' => 'El monto del abono excede el saldo pendiente',
                    'saldo_pendiente' => $apartado->saldo_pendiente
                ], 400);
            }

            // Guardar saldo anterior
            $saldoAnterior = $apartado->saldo_pendiente;

            // Crear el abono
            $abono = Abono::create([
                'apartado_id' => $apartado->id,
                'fecha_abono' => now(),
                'monto' => $request->monto,
                'saldo_anterior' => $saldoAnterior,
                'saldo_nuevo' => $saldoAnterior - $request->monto,
                'metodo_pago' => $request->metodo_pago,
                'comprobante' => $request->comprobante,
                'observaciones' => $request->observaciones,
                'usuario' => $request->usuario
            ]);

            // Actualizar saldo del apartado
            $apartado->saldo_pendiente = $saldoAnterior - $request->monto;

            // Si el saldo llega a 0, marcar como liquidado
            if ($apartado->saldo_pendiente <= 0) {
                $apartado->estado = 'liquidado';
            }

            $apartado->save();

            // Recargar relaciones
            $apartado->load(['cliente', 'detalles.libro', 'abonos']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Abono registrado exitosamente',
                'data' => [
                    'abono' => [
                        'id' => $abono->id,
                        'fecha_abono' => $abono->fecha_abono->format('Y-m-d'),
                        'monto' => $abono->monto,
                        'saldo_anterior' => $abono->saldo_anterior,
                        'saldo_nuevo' => $abono->saldo_nuevo,
                        'metodo_pago' => $abono->metodo_pago,
                        'comprobante' => $abono->comprobante,
                        'observaciones' => $abono->observaciones
                    ],
                    'apartado' => $this->formatearApartado($apartado)
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el abono',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de abonos de un apartado
     * GET /api/v1/movil/apartados/{apartado_id}/abonos
     */
    public function historialAbonos($apartado_id)
    {
        try {
            $apartado = Apartado::with(['cliente', 'abonos'])
                ->findOrFail($apartado_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'apartado' => [
                        'id' => $apartado->id,
                        'folio' => $apartado->folio,
                        'cliente' => $apartado->cliente->nombre,
                        'monto_total' => $apartado->monto_total,
                        'saldo_pendiente' => $apartado->saldo_pendiente,
                        'estado' => $apartado->estado
                    ],
                    'abonos' => $apartado->abonos->map(function ($abono) {
                        return [
                            'id' => $abono->id,
                            'fecha_abono' => $abono->fecha_abono->format('Y-m-d'),
                            'monto' => $abono->monto,
                            'saldo_anterior' => $abono->saldo_anterior,
                            'saldo_nuevo' => $abono->saldo_nuevo,
                            'metodo_pago' => $abono->metodo_pago,
                            'metodo_pago_label' => $abono->getMetodoPagoLabel(),
                            'comprobante' => $abono->comprobante,
                            'observaciones' => $abono->observaciones,
                            'usuario' => $abono->usuario
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial de abonos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatear datos del apartado para la respuesta
     */
    private function formatearApartado($apartado)
    {
        return [
            'id' => $apartado->id,
            'folio' => $apartado->folio,
            'cliente' => [
                'id' => $apartado->cliente->id,
                'nombre' => $apartado->cliente->nombre,
                'telefono' => $apartado->cliente->telefono
            ],
            'fecha_apartado' => $apartado->fecha_apartado->format('Y-m-d'),
            'fecha_limite' => $apartado->fecha_limite->format('Y-m-d'),
            'monto_total' => $apartado->monto_total,
            'enganche' => $apartado->enganche,
            'saldo_pendiente' => $apartado->saldo_pendiente,
            'total_pagado' => $apartado->total_pagado,
            'porcentaje_pagado' => $apartado->porcentaje_pagado,
            'estado' => $apartado->estado,
            'observaciones' => $apartado->observaciones,
            'libros' => $apartado->detalles->map(function ($detalle) {
                return [
                    'codigo' => $detalle->libro->codigo,
                    'titulo' => $detalle->libro->titulo,
                    'precio_unitario' => $detalle->precio_unitario,
                    'cantidad' => $detalle->cantidad,
                    'subtotal' => $detalle->subtotal
                ];
            }),
            'total_abonos' => $apartado->abonos->count(),
            'ultimo_abono' => $apartado->abonos->isNotEmpty() 
                ? [
                    'fecha' => $apartado->abonos->last()->fecha_abono->format('Y-m-d'),
                    'monto' => $apartado->abonos->last()->monto
                ]
                : null
        ];
    }
}
