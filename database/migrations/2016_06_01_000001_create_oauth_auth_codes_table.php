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
        Schema::create('oauth_auth_codes', function (Blueprint $table) {
            $table->string('id', 100)->primary();

            // Índices para consultas rápidas
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('client_id')->index();

            $table->text('scopes')->nullable();

            // Mejora: valor por defecto para evitar estados indefinidos
            $table->boolean('revoked')->default(false);

            $table->dateTime('expires_at')->nullable();

            // (Opcional) índice compuesto si consultas por ambos con frecuencia
            $table->index(['user_id', 'client_id'], 'oauth_auth_codes_user_client_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_auth_codes');
    }
};
