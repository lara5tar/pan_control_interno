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
        Schema::create('apartados', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_apartado');
            $table->string('descripcion')->nullable();
            $table->enum('estado', ['activo', 'completado', 'cancelado'])->default('activo');
            $table->string('usuario')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // Tabla pivote para la relación muchos a muchos entre apartados y libros
        Schema::create('apartado_libro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartado_id')->constrained()->onDelete('cascade');
            $table->foreignId('libro_id')->constrained('libros')->onDelete('cascade');
            $table->integer('cantidad')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartado_libro');
        Schema::dropIfExists('apartados');
    }
};
