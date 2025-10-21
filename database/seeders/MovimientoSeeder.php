<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Libro;
use App\Models\Movimiento;
use Carbon\Carbon;

class MovimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $libros = Libro::all();

        if ($libros->isEmpty()) {
            $this->command->warn('No hay libros en la base de datos. Ejecuta LibroSeeder primero.');
            return;
        }

        // Array de movimientos de ejemplo
        $movimientos = [
            // Compras iniciales
            [
                'libro_id' => $libros->random()->id,
                'tipo_movimiento' => 'entrada',
                'tipo_entrada' => 'compra',
                'cantidad' => 50,
                'precio_unitario' => 25.00,
                'observaciones' => 'Compra inicial de inventario',
                'usuario' => 'Admin',
                'created_at' => Carbon::now()->subDays(30)
            ],
            [
                'libro_id' => $libros->random()->id,
                'tipo_movimiento' => 'entrada',
                'tipo_entrada' => 'compra',
                'cantidad' => 30,
                'precio_unitario' => 18.50,
                'observaciones' => 'Segunda compra - proveedor A',
                'usuario' => 'Admin',
                'created_at' => Carbon::now()->subDays(25)
            ],
            
            // Ventas
            [
                'libro_id' => $libros->first()->id,
                'tipo_movimiento' => 'salida',
                'tipo_salida' => 'venta',
                'cantidad' => 5,
                'precio_unitario' => 35.00,
                'observaciones' => 'Venta local - Cliente #001',
                'usuario' => 'Vendedor 1',
                'created_at' => Carbon::now()->subDays(20)
            ],
            [
                'libro_id' => $libros->skip(1)->first()->id,
                'tipo_movimiento' => 'salida',
                'tipo_salida' => 'venta',
                'cantidad' => 3,
                'precio_unitario' => 28.00,
                'observaciones' => 'Venta online - Pedido #1234',
                'usuario' => 'Sistema',
                'created_at' => Carbon::now()->subDays(18)
            ],
            
            // Devoluciones
            [
                'libro_id' => $libros->random()->id,
                'tipo_movimiento' => 'entrada',
                'tipo_entrada' => 'devolucion',
                'cantidad' => 2,
                'observaciones' => 'Devolución por defecto de impresión',
                'usuario' => 'Admin',
                'created_at' => Carbon::now()->subDays(15)
            ],
            
            // Pérdidas
            [
                'libro_id' => $libros->random()->id,
                'tipo_movimiento' => 'salida',
                'tipo_salida' => 'perdida',
                'cantidad' => 1,
                'observaciones' => 'Ejemplar dañado por humedad',
                'usuario' => 'Almacén',
                'created_at' => Carbon::now()->subDays(12)
            ],
            
            // Donaciones recibidas
            [
                'libro_id' => $libros->random()->id,
                'tipo_movimiento' => 'entrada',
                'tipo_entrada' => 'donacion_recibida',
                'cantidad' => 10,
                'observaciones' => 'Donación de Fundación Cultura',
                'usuario' => 'Admin',
                'created_at' => Carbon::now()->subDays(10)
            ],
            
            // Préstamos
            [
                'libro_id' => $libros->random()->id,
                'tipo_movimiento' => 'salida',
                'tipo_salida' => 'prestamo',
                'cantidad' => 3,
                'observaciones' => 'Préstamo a Biblioteca Municipal por 30 días',
                'usuario' => 'Admin',
                'created_at' => Carbon::now()->subDays(8)
            ],
            
            // Ajuste positivo
            [
                'libro_id' => $libros->random()->id,
                'tipo_movimiento' => 'entrada',
                'tipo_entrada' => 'ajuste_positivo',
                'cantidad' => 2,
                'observaciones' => 'Ajuste por conteo físico - se encontraron ejemplares adicionales',
                'usuario' => 'Auditor',
                'created_at' => Carbon::now()->subDays(5)
            ],
            
            // Ajuste negativo
            [
                'libro_id' => $libros->random()->id,
                'tipo_movimiento' => 'salida',
                'tipo_salida' => 'ajuste_negativo',
                'cantidad' => 1,
                'observaciones' => 'Ajuste por conteo físico - diferencia de inventario',
                'usuario' => 'Auditor',
                'created_at' => Carbon::now()->subDays(4)
            ],
            
            // Donaciones entregadas
            [
                'libro_id' => $libros->random()->id,
                'tipo_movimiento' => 'salida',
                'tipo_salida' => 'donacion_entregada',
                'cantidad' => 5,
                'observaciones' => 'Donación a escuela primaria rural',
                'usuario' => 'Admin',
                'created_at' => Carbon::now()->subDays(3)
            ],
            
            // Más ventas recientes
            [
                'libro_id' => $libros->random()->id,
                'tipo_movimiento' => 'salida',
                'tipo_salida' => 'venta',
                'cantidad' => 7,
                'precio_unitario' => 32.00,
                'observaciones' => 'Venta mayorista - Cliente corporativo',
                'usuario' => 'Vendedor 2',
                'created_at' => Carbon::now()->subDays(2)
            ],
            
            // Compra reciente
            [
                'libro_id' => $libros->random()->id,
                'tipo_movimiento' => 'entrada',
                'tipo_entrada' => 'compra',
                'cantidad' => 25,
                'precio_unitario' => 22.00,
                'observaciones' => 'Reposición de stock - proveedor B',
                'usuario' => 'Admin',
                'created_at' => Carbon::now()->subDays(1)
            ],
        ];

        foreach ($movimientos as $movimientoData) {
            $libro = Libro::find($movimientoData['libro_id']);
            
            // Crear el movimiento
            $movimiento = Movimiento::create($movimientoData);
            
            // Actualizar el stock del libro
            if ($movimientoData['tipo_movimiento'] === 'entrada') {
                $libro->increment('stock', $movimientoData['cantidad']);
            } else {
                // Solo decrementar si hay stock suficiente
                if ($libro->stock >= $movimientoData['cantidad']) {
                    $libro->decrement('stock', $movimientoData['cantidad']);
                }
            }
        }

        $this->command->info('✅ Movimientos de ejemplo creados exitosamente!');
        $this->command->info('📊 Total: ' . count($movimientos) . ' movimientos');
    }
}
