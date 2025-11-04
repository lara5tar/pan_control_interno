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
        Schema::table('ventas', function (Blueprint $table) {
            $table->boolean('es_a_plazos')->default(false)->after('tipo_pago');
            $table->decimal('total_pagado', 10, 2)->default(0)->after('es_a_plazos');
            $table->enum('estado_pago', ['pendiente', 'parcial', 'completado'])->default('completado')->after('total_pagado');
            $table->date('fecha_limite')->nullable()->after('estado_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['es_a_plazos', 'total_pagado', 'estado_pago', 'fecha_limite']);
        });
    }
};
