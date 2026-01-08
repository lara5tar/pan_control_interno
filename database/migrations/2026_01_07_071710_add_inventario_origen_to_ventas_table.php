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
            // Tipo de inventario desde donde se realizó la venta
            $table->enum('tipo_inventario', ['general', 'subinventario'])->default('general')->after('usuario');
            
            // Subinventario específico (solo si tipo_inventario = 'subinventario')
            $table->foreignId('subinventario_id')->nullable()->after('tipo_inventario')->constrained('subinventarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['subinventario_id']);
            $table->dropColumn(['tipo_inventario', 'subinventario_id']);
        });
    }
};
