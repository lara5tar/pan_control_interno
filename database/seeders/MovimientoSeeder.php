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

        $this->command->info('🔄 Generando movimientos de inventario...');
        
        $usuarios = ['Admin', 'Vendedor 1', 'Vendedor 2', 'Sistema', 'Almacén', 'Auditor'];
        $totalMovimientos = 0;

        // 1. Generar compras iniciales de stock (hace 6 meses a 3 meses)
        $this->command->info('📦 Generando compras iniciales...');
        foreach ($libros as $libro) {
            $numCompras = rand(2, 4);
            for ($i = 0; $i < $numCompras; $i++) {
                $cantidad = rand(50, 200);
                $movimiento = Movimiento::create([
                    'libro_id' => $libro->id,
                    'tipo_movimiento' => 'entrada',
                    'tipo_entrada' => 'compra',
                    'cantidad' => $cantidad,
                    'precio_unitario' => $libro->precio * 0.6, // Precio de compra (60% del precio de venta)
                    'observaciones' => 'Compra inicial de inventario - Proveedor ' . chr(65 + rand(0, 4)),
                    'usuario' => $usuarios[array_rand($usuarios)],
                    'created_at' => Carbon::now()->subDays(rand(90, 180))->subHours(rand(0, 23))
                ]);
                $libro->increment('stock', $cantidad);
                $totalMovimientos++;
            }
        }

        // 2. Generar ventas aleatorias (últimos 3 meses)
        $this->command->info('💰 Generando ventas...');
        $numVentas = rand(80, 150);
        for ($i = 0; $i < $numVentas; $i++) {
            $libro = $libros->random();
            if ($libro->stock > 0) {
                $cantidad = min(rand(1, 15), $libro->stock);
                $movimiento = Movimiento::create([
                    'libro_id' => $libro->id,
                    'tipo_movimiento' => 'salida',
                    'tipo_salida' => 'venta',
                    'cantidad' => $cantidad,
                    'precio_unitario' => $libro->precio,
                    'observaciones' => rand(0, 1) ? 'Venta local - Cliente #' . rand(100, 999) : 'Venta online - Pedido #' . rand(1000, 9999),
                    'usuario' => $usuarios[array_rand($usuarios)],
                    'created_at' => Carbon::now()->subDays(rand(1, 90))->subHours(rand(0, 23))
                ]);
                $libro->decrement('stock', $cantidad);
                $totalMovimientos++;
            }
        }

        // 3. Generar devoluciones (últimos 2 meses)
        $this->command->info('↩️ Generando devoluciones...');
        $numDevoluciones = rand(15, 30);
        for ($i = 0; $i < $numDevoluciones; $i++) {
            $libro = $libros->random();
            $cantidad = rand(1, 5);
            $razones = [
                'Devolución por defecto de impresión',
                'Cliente cambió de opinión',
                'Devolución de proveedor por exceso',
                'Devolución por error en pedido',
                'Producto devuelto en garantía'
            ];
            $movimiento = Movimiento::create([
                'libro_id' => $libro->id,
                'tipo_movimiento' => 'entrada',
                'tipo_entrada' => 'devolucion',
                'cantidad' => $cantidad,
                'observaciones' => $razones[array_rand($razones)],
                'usuario' => $usuarios[array_rand($usuarios)],
                'created_at' => Carbon::now()->subDays(rand(1, 60))->subHours(rand(0, 23))
            ]);
            $libro->increment('stock', $cantidad);
            $totalMovimientos++;
        }

        // 4. Generar donaciones recibidas (últimos 3 meses)
        $this->command->info('🎁 Generando donaciones recibidas...');
        $numDonacionesRecibidas = rand(10, 20);
        for ($i = 0; $i < $numDonacionesRecibidas; $i++) {
            $libro = $libros->random();
            $cantidad = rand(5, 30);
            $donantes = [
                'Fundación Cultural',
                'Editorial Santillana',
                'Donación anónima',
                'Universidad Nacional',
                'Ministerio de Cultura'
            ];
            $movimiento = Movimiento::create([
                'libro_id' => $libro->id,
                'tipo_movimiento' => 'entrada',
                'tipo_entrada' => 'donacion_recibida',
                'cantidad' => $cantidad,
                'observaciones' => 'Donación de: ' . $donantes[array_rand($donantes)],
                'usuario' => 'Admin',
                'created_at' => Carbon::now()->subDays(rand(1, 90))->subHours(rand(0, 23))
            ]);
            $libro->increment('stock', $cantidad);
            $totalMovimientos++;
        }

        // 5. Generar préstamos (últimos 2 meses)
        $this->command->info('📚 Generando préstamos...');
        $numPrestamos = rand(20, 40);
        for ($i = 0; $i < $numPrestamos; $i++) {
            $libro = $libros->random();
            if ($libro->stock > 0) {
                $cantidad = min(rand(1, 5), $libro->stock);
                $instituciones = [
                    'Biblioteca Municipal',
                    'Escuela Primaria #12',
                    'Colegio San José',
                    'Centro Cultural',
                    'Universidad Pedagógica'
                ];
                $movimiento = Movimiento::create([
                    'libro_id' => $libro->id,
                    'tipo_movimiento' => 'salida',
                    'tipo_salida' => 'prestamo',
                    'cantidad' => $cantidad,
                    'observaciones' => 'Préstamo a ' . $instituciones[array_rand($instituciones)] . ' por ' . rand(15, 60) . ' días',
                    'usuario' => 'Admin',
                    'created_at' => Carbon::now()->subDays(rand(1, 60))->subHours(rand(0, 23))
                ]);
                $libro->decrement('stock', $cantidad);
                $totalMovimientos++;
            }
        }

        // 6. Generar donaciones entregadas (últimos 3 meses)
        $this->command->info('🤝 Generando donaciones entregadas...');
        $numDonacionesEntregadas = rand(15, 25);
        for ($i = 0; $i < $numDonacionesEntregadas; $i++) {
            $libro = $libros->random();
            if ($libro->stock > 0) {
                $cantidad = min(rand(3, 20), $libro->stock);
                $beneficiarios = [
                    'Escuela Rural San Antonio',
                    'Biblioteca Comunitaria',
                    'Centro de Alfabetización',
                    'Fundación Leer',
                    'Hospital Infantil'
                ];
                $movimiento = Movimiento::create([
                    'libro_id' => $libro->id,
                    'tipo_movimiento' => 'salida',
                    'tipo_salida' => 'donacion_entregada',
                    'cantidad' => $cantidad,
                    'observaciones' => 'Donación a: ' . $beneficiarios[array_rand($beneficiarios)],
                    'usuario' => 'Admin',
                    'created_at' => Carbon::now()->subDays(rand(1, 90))->subHours(rand(0, 23))
                ]);
                $libro->decrement('stock', $cantidad);
                $totalMovimientos++;
            }
        }

        // 7. Generar pérdidas (últimos 6 meses)
        $this->command->info('⚠️ Generando pérdidas...');
        $numPerdidas = rand(5, 15);
        for ($i = 0; $i < $numPerdidas; $i++) {
            $libro = $libros->random();
            if ($libro->stock > 0) {
                $cantidad = min(rand(1, 3), $libro->stock);
                $causas = [
                    'Ejemplar dañado por humedad',
                    'Páginas rotas - no recuperable',
                    'Daño por agua en almacén',
                    'Pérdida en transporte',
                    'Deterioro por almacenamiento prolongado'
                ];
                $movimiento = Movimiento::create([
                    'libro_id' => $libro->id,
                    'tipo_movimiento' => 'salida',
                    'tipo_salida' => 'perdida',
                    'cantidad' => $cantidad,
                    'observaciones' => $causas[array_rand($causas)],
                    'usuario' => 'Almacén',
                    'created_at' => Carbon::now()->subDays(rand(1, 180))->subHours(rand(0, 23))
                ]);
                $libro->decrement('stock', $cantidad);
                $totalMovimientos++;
            }
        }

        // 8. Generar ajustes de inventario (último mes)
        $this->command->info('🔧 Generando ajustes de inventario...');
        $numAjustes = rand(5, 15);
        for ($i = 0; $i < $numAjustes; $i++) {
            $libro = $libros->random();
            $esPositivo = rand(0, 1);
            
            if ($esPositivo) {
                // Ajuste positivo
                $cantidad = rand(1, 5);
                $movimiento = Movimiento::create([
                    'libro_id' => $libro->id,
                    'tipo_movimiento' => 'entrada',
                    'tipo_entrada' => 'ajuste_positivo',
                    'cantidad' => $cantidad,
                    'observaciones' => 'Ajuste por conteo físico - se encontraron ejemplares adicionales',
                    'usuario' => 'Auditor',
                    'created_at' => Carbon::now()->subDays(rand(1, 30))->subHours(rand(0, 23))
                ]);
                $libro->increment('stock', $cantidad);
            } else {
                // Ajuste negativo
                if ($libro->stock > 0) {
                    $cantidad = min(rand(1, 3), $libro->stock);
                    $movimiento = Movimiento::create([
                        'libro_id' => $libro->id,
                        'tipo_movimiento' => 'salida',
                        'tipo_salida' => 'ajuste_negativo',
                        'cantidad' => $cantidad,
                        'observaciones' => 'Ajuste por conteo físico - diferencia de inventario no justificada',
                        'usuario' => 'Auditor',
                        'created_at' => Carbon::now()->subDays(rand(1, 30))->subHours(rand(0, 23))
                    ]);
                    $libro->decrement('stock', $cantidad);
                }
            }
            $totalMovimientos++;
        }

        // Array de movimientos de ejemplo específicos (mantener algunos originales)
        $this->command->info('📝 Generando movimientos de ejemplo específicos...');
        $movimientosEjemplo = [
            [
                'libro_id' => $libros->first()->id,
                'tipo_movimiento' => 'entrada',
                'tipo_entrada' => 'compra',
                'cantidad' => 50,
                'precio_unitario' => 25.00,
                'observaciones' => 'Compra inicial de inventario - lote especial',
                'usuario' => 'Admin',
                'created_at' => Carbon::now()->subDays(30)
            ],
            [
                'libro_id' => $libros->skip(1)->first()->id,
                'tipo_movimiento' => 'salida',
                'tipo_salida' => 'venta',
                'cantidad' => 5,
                'precio_unitario' => 35.00,
                'observaciones' => 'Venta local - Cliente frecuente #001',
                'usuario' => 'Vendedor 1',
                'created_at' => Carbon::now()->subDays(15)
            ],
            [
                'libro_id' => $libros->skip(2)->first()->id,
                'tipo_movimiento' => 'entrada',
                'tipo_entrada' => 'donacion_recibida',
                'cantidad' => 10,
                'observaciones' => 'Donación especial de Fundación Cultura',
                'usuario' => 'Admin',
                'created_at' => Carbon::now()->subDays(5)
            ]
        ];

        foreach ($movimientosEjemplo as $movimientoData) {
            $libro = Libro::find($movimientoData['libro_id']);
            
            // Crear el movimiento
            $movimiento = Movimiento::create($movimientoData);
            
            // Actualizar el stock según el tipo de movimiento
            if ($movimientoData['tipo_movimiento'] === 'entrada') {
                $libro->increment('stock', $movimientoData['cantidad']);
            } else {
                $libro->decrement('stock', $movimientoData['cantidad']);
            }
            
            $totalMovimientos++;
        }

        $this->command->newLine();
        $this->command->info('✅ Seeder completado exitosamente!');
        $this->command->info('📊 Total de movimientos generados: ' . $totalMovimientos);
        $this->command->info('📚 Total de libros: ' . $libros->count());
        
        // Mostrar estadísticas de stock
        $this->command->newLine();
        $this->command->info('📈 Estadísticas de stock:');
        $this->command->info('   • Stock mínimo: ' . $libros->min('stock'));
        $this->command->info('   • Stock máximo: ' . $libros->max('stock'));
        $this->command->info('   • Stock promedio: ' . round($libros->avg('stock'), 2));
    }
}
