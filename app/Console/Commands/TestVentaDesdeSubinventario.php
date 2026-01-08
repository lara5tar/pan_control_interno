<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Venta;
use App\Models\SubInventario;
use App\Models\Movimiento;

class TestVentaDesdeSubinventario extends Command
{
    protected $signature = 'test:venta-subinventario';
    protected $description = 'Test para verificar que una venta se puede crear desde un subinventario';

    public function handle()
    {
        $this->info('🚀 Iniciando test de venta desde subinventario...');
        $this->newLine();
        
        // Obtener un subinventario
        $subinventario = SubInventario::with('libros')->first();

        if (!$subinventario) {
            $this->error('❌ No hay subinventarios disponibles');
            return 1;
        }

        if ($subinventario->libros->count() === 0) {
            $this->error('❌ El subinventario no tiene libros asignados');
            return 1;
        }

        $libro = $subinventario->libros->first();
        
        $this->info("📦 Subinventario: #{$subinventario->id} - " . ($subinventario->descripcion ?? 'Sin descripción'));
        $this->info("📚 Usando libro del subinventario: {$libro->titulo}");
        $this->newLine();

        // Crear venta desde subinventario
        $venta = Venta::create([
            'fecha_venta' => now(),
            'tipo_pago' => 'contado',
            'tiene_envio' => false,
            'tipo_inventario' => 'subinventario',  // ← Desde subinventario
            'subinventario_id' => $subinventario->id,  // ← ID del subinventario
            'descuento_global' => 0,
        ]);

        $this->line("✅ Venta creada con ID: {$venta->id}");

        // Crear movimiento
        $movimiento = Movimiento::create([
            'libro_id' => $libro->id,
            'tipo_movimiento' => 'salida',
            'venta_id' => $venta->id,
            'cantidad' => 1,
            'precio_unitario' => $libro->precio,
            'descuento' => 0,
            'fecha_movimiento' => now(),
        ]);

        $this->line('✅ Movimiento creado');
        $this->newLine();

        // VERIFICAR
        $ventaVerificada = Venta::with('subinventario')->find($venta->id);

        $this->info('═══════════════════════════════════════');
        $this->info('VERIFICACIÓN DE DATOS GUARDADOS');
        $this->info('═══════════════════════════════════════');
        $this->line("ID:                  {$ventaVerificada->id}");
        $this->line("Fecha:               {$ventaVerificada->fecha_venta}");
        $this->line("Tipo Inventario:     {$ventaVerificada->tipo_inventario}");
        $this->line("Subinventario ID:    {$ventaVerificada->subinventario_id}");
        
        if ($ventaVerificada->subinventario) {
            $this->line("Subinventario:       #{$ventaVerificada->subinventario->id} - " . ($ventaVerificada->subinventario->descripcion ?? 'Sin descripción'));
        }
        
        $this->line("Tipo Pago:           {$ventaVerificada->tipo_pago}");
        $this->line("Total Movimientos:   {$ventaVerificada->movimientos->count()}");
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        if ($ventaVerificada->tipo_inventario === 'subinventario' && $ventaVerificada->subinventario_id == $subinventario->id) {
            $this->info('✅ ¡TEST EXITOSO! La venta se guardó correctamente con tipo_inventario=subinventario y subinventario_id');
            return 0;
        } else {
            $this->error('❌ ERROR: Los campos no tienen los valores esperados');
            return 1;
        }
    }
}

