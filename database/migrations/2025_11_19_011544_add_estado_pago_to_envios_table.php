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
        Schema::table('envios', function (Blueprint $table) {
            $table->enum('estado_pago', ['pendiente', 'pagado'])->default('pendiente')->after('estado');
            $table->string('comprobante_pago')->nullable()->after('comprobante'); // Archivo del comprobante de pago
            $table->date('fecha_pago')->nullable()->after('comprobante_pago'); // Fecha en que se realizó el pago
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            $table->dropColumn(['estado_pago', 'comprobante_pago', 'fecha_pago']);
        });
    }
};
