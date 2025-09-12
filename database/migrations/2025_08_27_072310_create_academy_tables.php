<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // DOCENTES (perfil enlazado a users)
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();

            // users YA existe -> FK segura
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // branches TODAVÍA NO existe según el orden -> evitar constrained aquí
            $table->unsignedBigInteger('branch_id');
            $table->index('branch_id', 'teachers_branch_idx');

            // Opcionales (puedes depender solo de users.name si gustas)
            $table->string('nombres')->nullable();
            $table->string('telefono', 30)->nullable();

            $table->timestamps();
        });

        // CURSOS (catálogo)
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre');
            $table->unsignedTinyInteger('creditos')->default(0);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // OFERTAS/SECCIONES (curso + docente + sede + grado + nivel + calendario)
        Schema::create('offerings', function (Blueprint $table) {
            $table->id();

            // courses YA existe en esta misma migración -> FK segura
            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            // teachers YA existe en esta misma migración -> FK segura
            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->restrictOnDelete();

            // branches aún no (por el orden) -> sin constrained aquí
            $table->unsignedBigInteger('branch_id');
            $table->index('branch_id');

            // Requisito del inge
            $table->enum('grade', ['Novatos','Expertos']);
            $table->enum('level', ['Principiantes I','Principiantes II','Avanzados I','Avanzados II']);

            // Calendario académico (usar tipo portable)
            $table->unsignedSmallInteger('anio');
            $table->string('ciclo', 10); // I, II, Verano, etc.

            $table->unsignedSmallInteger('cupo')->default(30);
            $table->string('horario')->nullable();

            $table->timestamps();

            $table->unique(
                ['course_id','teacher_id','branch_id','grade','level','anio','ciclo'],
                'offering_unique'
            );
            $table->index(['branch_id','teacher_id','grade','level'], 'offerings_filters_idx');
        });

        // INSCRIPCIONES
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            // offerings YA existe en esta migración -> FK segura
            $table->foreignId('offering_id')
                ->constrained('offerings')
                ->cascadeOnDelete();

            // students NO existe aún (por orden) -> sin constrained aquí
            $table->unsignedBigInteger('student_id');
            $table->index('student_id', 'enrollments_student_idx');

            $table->timestamp('fecha')->useCurrent();
            $table->enum('status', ['activa','retirada','finalizada'])->default('activa');

            $table->timestamps();

            $table->unique(['offering_id','student_id'], 'enrollment_unique');
            $table->index(['offering_id','created_at'], 'enrollments_report_idx');
        });

        // NOTAS (3 rubros típicos; si quieres 1 sola nota, cambia aquí)
        Schema::create('grades', function (Blueprint $table) {
            $table->id();

            // enrollments YA existe en esta migración -> FK segura
            $table->foreignId('enrollment_id')
                ->constrained('enrollments')
                ->cascadeOnDelete();

            $table->decimal('parcial1', 5, 2)->nullable();
            $table->decimal('parcial2', 5, 2)->nullable();
            $table->decimal('final',    5, 2)->nullable();

            $table->string('observaciones')->nullable();

            $table->timestamps();

            $table->index('enrollment_id', 'grades_enrollment_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('offerings');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('teachers');

        // Importante: NO borrar aquí students ni branches,
        // se crean en migraciones separadas.
    }
};
