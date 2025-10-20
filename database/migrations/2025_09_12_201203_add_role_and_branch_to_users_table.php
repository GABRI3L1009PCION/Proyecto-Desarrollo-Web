<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'branch_id')) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('branches')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin','catedratico','estudiante','secretaria'])
                    ->default('estudiante')
                    ->after('password');
            }

            // Crear índice combinado solo si ambas columnas existen
            if (Schema::hasColumn('users', 'branch_id') && Schema::hasColumn('users', 'role')) {
                $table->index(['branch_id', 'role'], 'users_branch_role_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Primero borrar el índice
            if (Schema::hasColumn('users', 'branch_id') && Schema::hasColumn('users', 'role')) {
                $table->dropIndex('users_branch_role_idx');
            }

            // Luego eliminar las columnas
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }

            if (Schema::hasColumn('users', 'branch_id')) {
                $table->dropConstrainedForeignId('branch_id');
            }
        });
    }
};
