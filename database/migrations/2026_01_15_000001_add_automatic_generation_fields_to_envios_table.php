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
            $table->enum('tipo_generacion', ['manual', 'automatico'])->default('manual')->after('estado_pago');
            $table->date('periodo_inicio')->nullable()->after('tipo_generacion');
            $table->date('periodo_fin')->nullable()->after('periodo_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            $table->dropColumn(['tipo_generacion', 'periodo_inicio', 'periodo_fin']);
        });
    }
};
