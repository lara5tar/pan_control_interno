<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero cambiamos la columna a VARCHAR temporal
        DB::statement('ALTER TABLE pagos MODIFY COLUMN metodo_pago VARCHAR(50)');
        
        // Actualizamos todos los valores existentes a 'contado'
        DB::table('pagos')->update(['metodo_pago' => 'contado']);
        
        // Finalmente cambiamos a ENUM con los nuevos valores
        DB::statement("ALTER TABLE pagos MODIFY COLUMN metodo_pago ENUM('contado', 'credito') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'tarjeta', 'deposito'])->change();
        });
    }
};
