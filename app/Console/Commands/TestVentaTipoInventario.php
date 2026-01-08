<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Venta;
use App\Models\Libro;
use App\Models\Movimiento;

class TestVentaTipoInventario extends Command
{
    protected $signature = 'test:venta-tipo-inventario';
    protected $description = 'Test para verificar que tipo_inventario y subinventario_id se guardan correctamente';

    public function handle()
    {
        $this->info('🚀 Iniciando test de creación de venta...');
        $this->newLine();
        
        // Obtener un libro disponible
        $libro = Libro::where('stock', '>', 0)->first();

        if (!$libro) {
            $this->error('❌ No hay libros con stock disponible');
            return 1;
        }

        $this->info("📚 Usando libro: {$libro->titulo} (Stock: {$libro->stock})");
        $this->newLine();

        // Crear venta
        $venta = Venta::create([
            'fecha_venta' => now(),
            'tipo_pago' => 'contado',
            'tiene_envio' => false,
            'tipo_inventario' => 'general',  // ← Campo que queremos verificar
            'subinventario_id' => null,
            'descuento_global' => 0,
        ]);

        $this->line("✅ Venta creada con ID: {$venta->id}");

        // Crear movimiento (libro vendido)
        $movimiento = Movimiento::create([
            'libro_id' => $libro->id,
            'tipo_movimiento' => 'salida',
            'venta_id' => $venta->id,
            'cantidad' => 1,
            'precio_unitario' => $libro->precio,
            'descuento' => 0,
            'fecha_movimiento' => now(),
        ]);

        $this->line('✅ Movimiento creado para la venta');

        // Actualizar stock
        $libro->stock -= 1;
        $libro->save();

        $this->line("✅ Stock actualizado: {$libro->stock}");
        $this->newLine();

        // VERIFICAR que se guardó correctamente el tipo_inventario
        $ventaVerificada = Venta::find($venta->id);

        $this->info('═══════════════════════════════════════');
        $this->info('VERIFICACIÓN DE DATOS GUARDADOS');
        $this->info('═══════════════════════════════════════');
        $this->line("ID:                  {$ventaVerificada->id}");
        $this->line("Fecha:               {$ventaVerificada->fecha_venta}");
        $this->line("Tipo Inventario:     {$ventaVerificada->tipo_inventario}");
        $this->line("Subinventario ID:    " . ($ventaVerificada->subinventario_id ?? 'NULL'));
        $this->line("Tipo Pago:           {$ventaVerificada->tipo_pago}");
        $this->line("Total Movimientos:   {$ventaVerificada->movimientos->count()}");
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        if ($ventaVerificada->tipo_inventario === 'general' && is_null($ventaVerificada->subinventario_id)) {
            $this->info('✅ ¡TEST EXITOSO! Los campos tipo_inventario y subinventario_id se guardaron correctamente');
            return 0;
        } else {
            $this->error('❌ ERROR: Los campos no tienen los valores esperados');
            return 1;
        }
    }
}

