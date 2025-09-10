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
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnUpdate()->restrictOnDelete();
                $table->string('nombres', 150);
                $table->string('email', 150)->unique();
                $table->string('telefono', 30)->nullable();
                $table->date('fecha_nacimiento')->nullable();
                $table->timestamps();

                $table->index('branch_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
