<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table) {
                $table->id();

                // Relación con el usuario (1 user = 1 alumno)
                $table->foreignId('user_id')
                    ->unique()
                    ->constrained('users')
                    ->cascadeOnDelete();

                // Sucursal a la que pertenece
                $table->foreignId('branch_id')
                    ->constrained('branches')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                // Datos opcionales
                $table->string('nombres', 150);
                $table->string('telefono', 30)->nullable();
                $table->date('fecha_nacimiento')->nullable();

                // Requisitos del inge
                $table->enum('grade', ['Novatos','Expertos']);
                $table->enum('level', ['Principiantes I','Principiantes II','Avanzados I','Avanzados II']);

                $table->timestamps();

                // Índices útiles
                $table->index(['branch_id','grade','level'], 'students_branch_grade_level_idx');
                $table->index('user_id', 'students_user_idx');
                $table->index('branch_id', 'students_branch_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
