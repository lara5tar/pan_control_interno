<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla principal de apartados
        Schema::create('apartados', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique(); // AP-2025-0001
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->date('fecha_apartado');
            $table->decimal('monto_total', 10, 2);
            $table->decimal('enganche', 10, 2)->default(0); // Primer pago/anticipo
            $table->decimal('saldo_pendiente', 10, 2);
            $table->date('fecha_limite')->nullable(); // Fecha límite para liquidar
            $table->enum('estado', ['activo', 'liquidado', 'cancelado'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->string('usuario'); // Usuario que registró
            $table->foreignId('venta_id')->nullable()->constrained()->onDelete('set null'); // Se llena al liquidar
            $table->timestamps();
        });

        // Detalle de libros apartados
        Schema::create('apartado_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartado_id')->constrained('apartados')->onDelete('cascade');
            $table->foreignId('libro_id')->constrained('libros')->onDelete('cascade');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento', 5, 2)->default(0); // Descuento en porcentaje
            $table->decimal('subtotal', 10, 2); // precio_unitario * cantidad con descuento
            $table->timestamps();
        });

        // Historial de abonos
        Schema::create('abonos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartado_id')->constrained('apartados')->onDelete('cascade');
            $table->date('fecha_abono');
            $table->decimal('monto', 10, 2);
            $table->decimal('saldo_anterior', 10, 2);
            $table->decimal('saldo_nuevo', 10, 2);
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'tarjeta'])->default('efectivo');
            $table->string('comprobante')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('usuario'); // Usuario que registró
            $table->timestamps();
        });

        // Agregar campo stock_apartado a libros (para controlar inventario)
        Schema::table('libros', function (Blueprint $table) {
            $table->integer('stock_apartado')->default(0)->after('stock_subinventario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('libros', function (Blueprint $table) {
            $table->dropColumn('stock_apartado');
        });
        
        Schema::dropIfExists('abonos');
        Schema::dropIfExists('apartado_detalles');
        Schema::dropIfExists('apartados');
    }
};
