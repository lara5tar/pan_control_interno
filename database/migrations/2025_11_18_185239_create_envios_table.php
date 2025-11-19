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
        Schema::create('envios', function (Blueprint $table) {
            $table->id();
            $table->string('guia')->nullable(); // Número de guía de FedEx o referencia
            $table->date('fecha_envio'); // Fecha del envío
            $table->decimal('monto_a_pagar', 10, 2)->default(0); // Monto a pagar a FedEx
            $table->string('comprobante')->nullable(); // Ruta del archivo de factura/comprobante
            $table->text('notas')->nullable(); // Notas adicionales
            $table->enum('estado', ['pendiente', 'en_transito', 'entregado', 'cancelado'])->default('pendiente');
            $table->string('usuario')->nullable(); // Usuario que registró el envío
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envios');
    }
};
