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
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('libro_id')->constrained('libros')->onDelete('cascade');
            $table->enum('tipo_movimiento', ['entrada', 'salida']);
            $table->enum('tipo_entrada', [
                'compra',           // Compra de nuevos ejemplares
                'devolucion',       // Devolución de cliente
                'ajuste_positivo',  // Ajuste de inventario (conteo físico encontró más)
                'donacion_recibida' // Donación que recibimos
            ])->nullable();
            $table->enum('tipo_salida', [
                'venta',            // Venta a cliente
                'perdida',          // Pérdida por robo, daño, etc.
                'ajuste_negativo',  // Ajuste de inventario (conteo físico encontró menos)
                'donacion_entregada', // Donación que hacemos
                'prestamo'          // Préstamo a biblioteca u otra entidad
            ])->nullable();
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('usuario')->nullable(); // Usuario que realizó el movimiento
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
