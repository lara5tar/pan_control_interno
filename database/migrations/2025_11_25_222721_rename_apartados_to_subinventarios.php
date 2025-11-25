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
        // 1. Renombrar tabla apartados a subinventarios
        Schema::rename('apartados', 'subinventarios');
        
        // 2. Renombrar tabla apartado_libro a subinventario_libro
        Schema::rename('apartado_libro', 'subinventario_libro');
        
        // 3. Renombrar columna fecha_apartado a fecha_subinventario en subinventarios
        Schema::table('subinventarios', function (Blueprint $table) {
            $table->renameColumn('fecha_apartado', 'fecha_subinventario');
        });
        
        // 4. Renombrar columna apartado_id a subinventario_id en subinventario_libro
        Schema::table('subinventario_libro', function (Blueprint $table) {
            $table->renameColumn('apartado_id', 'subinventario_id');
        });
        
        // 5. Renombrar columna stock_apartado a stock_subinventario en libros
        Schema::table('libros', function (Blueprint $table) {
            $table->renameColumn('stock_apartado', 'stock_subinventario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir en orden inverso
        
        // 5. Revertir stock_subinventario a stock_apartado
        Schema::table('libros', function (Blueprint $table) {
            $table->renameColumn('stock_subinventario', 'stock_apartado');
        });
        
        // 4. Revertir subinventario_id a apartado_id
        Schema::table('subinventario_libro', function (Blueprint $table) {
            $table->renameColumn('subinventario_id', 'apartado_id');
        });
        
        // 3. Revertir fecha_subinventario a fecha_apartado
        Schema::table('subinventarios', function (Blueprint $table) {
            $table->renameColumn('fecha_subinventario', 'fecha_apartado');
        });
        
        // 2. Revertir subinventario_libro a apartado_libro
        Schema::rename('subinventario_libro', 'apartado_libro');
        
        // 1. Revertir subinventarios a apartados
        Schema::rename('subinventarios', 'apartados');
    }
};
