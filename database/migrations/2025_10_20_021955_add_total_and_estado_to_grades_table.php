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
        Schema::table('grades', function (Blueprint $table) {
            // 🔹 Agregar columnas nuevas
            $table->decimal('total', 5, 2)
                ->nullable()
                ->after('final');

            $table->enum('estado', ['Aprobado', 'Recuperación', 'Reprobado'])
                ->nullable()
                ->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            // 🔹 Eliminar las columnas en caso de rollback
            $table->dropColumn(['total', 'estado']);
        });
    }
};
