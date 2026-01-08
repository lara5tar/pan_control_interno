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
        Schema::create('subinventario_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subinventario_id')->constrained('subinventarios')->onDelete('cascade');
            $table->string('cod_congregante'); // Código del congregante desde la API externa
            $table->string('nombre_congregante'); // Nombre completo del congregante (cache)
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['subinventario_id', 'cod_congregante']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subinventario_user');
    }
};
