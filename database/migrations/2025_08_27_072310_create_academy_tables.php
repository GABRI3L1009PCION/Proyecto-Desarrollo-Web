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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('nombres');
            $table->string('email')->unique();
            $table->string('telefono', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('nombres');
            $table->string('email')->unique();
            $table->string('telefono', 30)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre');
            $table->unsignedTinyInteger('creditos')->default(0);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // Oferta/Sección de un curso con docente y sede
        Schema::create('offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->year('anio');
            $table->string('ciclo', 10);   // I, II, Verano, etc.
            $table->unsignedSmallInteger('cupo')->default(30);
            $table->string('horario')->nullable();
            $table->timestamps();
            $table->unique(['course_id','teacher_id','branch_id','anio','ciclo'], 'offering_unique');
        });

        // Inscripciones de alumnos a una oferta
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offering_id')->constrained('offerings')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamp('fecha')->useCurrent();
            $table->timestamps();
            $table->unique(['offering_id','student_id'], 'enrollment_unique');
        });

        // Notas de una inscripción
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->decimal('nota',5,2)->nullable();   // 0–100.00
            $table->string('observaciones')->nullable();
            $table->timestamps();
            $table->unique('enrollment_id'); // una nota por inscripción
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('offerings');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('students');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('branches');
    }

};
