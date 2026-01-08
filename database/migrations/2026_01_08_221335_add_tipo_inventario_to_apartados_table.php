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
        Schema::table('apartados', function (Blueprint $table) {
            $table->enum('tipo_inventario', ['general', 'subinventario'])->default('general')->after('usuario');
            $table->foreignId('subinventario_id')->nullable()->constrained('subinventarios')->onDelete('set null')->after('tipo_inventario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartados', function (Blueprint $table) {
            $table->dropForeign(['subinventario_id']);
            $table->dropColumn(['tipo_inventario', 'subinventario_id']);
        });
    }
};
