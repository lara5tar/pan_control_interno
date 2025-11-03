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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // Código único de venta: V-0001
            $table->string('cliente')->nullable(); // Nombre del cliente
            $table->date('fecha_venta'); // Fecha de la venta
            $table->enum('tipo_pago', ['contado', 'credito', 'mixto'])->default('contado');
            $table->decimal('subtotal', 10, 2)->default(0); // Suma de todos los movimientos
            $table->decimal('descuento_global', 5, 2)->default(0); // Descuento adicional en %
            $table->decimal('total', 10, 2)->default(0); // Total final
            $table->enum('estado', ['pendiente', 'completada', 'cancelada'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->string('usuario')->nullable(); // Usuario que registró
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
