<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ---------------------------
        // Passport: oauth_auth_codes
        // ---------------------------
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            if (
                Schema::hasTable('oauth_auth_codes') &&
                Schema::hasColumn('oauth_auth_codes', 'user_id') &&
                Schema::hasColumn('oauth_auth_codes', 'client_id') &&
                Schema::hasTable('users') &&
                Schema::hasTable('oauth_clients')
            ) {
                // Nombres explícitos de constraints
                $table->foreign('user_id', 'fk_oauth_auth_codes_user_id')
                    ->references('id')->on('users')
                    ->cascadeOnDelete();

                $table->foreign('client_id', 'fk_oauth_auth_codes_client_id')
                    ->references('id')->on('oauth_clients')
                    ->cascadeOnDelete();
            }
        });

        // ------------------------------
        // Passport: oauth_access_tokens
        // ------------------------------
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            if (
                Schema::hasTable('oauth_access_tokens') &&
                Schema::hasColumn('oauth_access_tokens', 'user_id') &&
                Schema::hasColumn('oauth_access_tokens', 'client_id') &&
                Schema::hasTable('users') &&
                Schema::hasTable('oauth_clients')
            ) {
                $table->foreign('user_id', 'fk_oauth_access_tokens_user_id')
                    ->references('id')->on('users')
                    ->cascadeOnDelete();

                $table->foreign('client_id', 'fk_oauth_access_tokens_client_id')
                    ->references('id')->on('oauth_clients')
                    ->cascadeOnDelete();
            }
        });

        // =========================================================
        // (Opcional pero recomendado) Academia: foreign keys tardías
        // =========================================================

        // teachers.branch_id -> branches.id
        Schema::table('teachers', function (Blueprint $table) {
            if (
                Schema::hasTable('teachers') &&
                Schema::hasColumn('teachers', 'branch_id') &&
                Schema::hasTable('branches')
            ) {
                $table->foreign('branch_id', 'fk_teachers_branch_id')
                    ->references('id')->on('branches')
                    ->restrictOnDelete();
            }
        });

        // offerings.branch_id -> branches.id
        Schema::table('offerings', function (Blueprint $table) {
            if (
                Schema::hasTable('offerings') &&
                Schema::hasColumn('offerings', 'branch_id') &&
                Schema::hasTable('branches')
            ) {
                $table->foreign('branch_id', 'fk_offerings_branch_id')
                    ->references('id')->on('branches')
                    ->restrictOnDelete();
            }
        });

        // enrollments.student_id -> students.id
        Schema::table('enrollments', function (Blueprint $table) {
            if (
                Schema::hasTable('enrollments') &&
                Schema::hasColumn('enrollments', 'student_id') &&
                Schema::hasTable('students')
            ) {
                $table->foreign('student_id', 'fk_enrollments_student_id')
                    ->references('id')->on('students')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Passport: oauth_auth_codes
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            if (Schema::hasTable('oauth_auth_codes')) {
                // Usamos los nombres explícitos que definimos arriba
                try { $table->dropForeign('fk_oauth_auth_codes_user_id'); } catch (\Throwable $e) {}
                try { $table->dropForeign('fk_oauth_auth_codes_client_id'); } catch (\Throwable $e) {}
            }
        });

        // Passport: oauth_access_tokens
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            if (Schema::hasTable('oauth_access_tokens')) {
                try { $table->dropForeign('fk_oauth_access_tokens_user_id'); } catch (\Throwable $e) {}
                try { $table->dropForeign('fk_oauth_access_tokens_client_id'); } catch (\Throwable $e) {}
            }
        });

        // Academia: teachers.branch_id
        Schema::table('teachers', function (Blueprint $table) {
            if (Schema::hasTable('teachers')) {
                try { $table->dropForeign('fk_teachers_branch_id'); } catch (\Throwable $e) {}
            }
        });

        // Academia: offerings.branch_id
        Schema::table('offerings', function (Blueprint $table) {
            if (Schema::hasTable('offerings')) {
                try { $table->dropForeign('fk_offerings_branch_id'); } catch (\Throwable $e) {}
            }
        });

        // Academia: enrollments.student_id
        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasTable('enrollments')) {
                try { $table->dropForeign('fk_enrollments_student_id'); } catch (\Throwable $e) {}
            }
        });
    }
};
