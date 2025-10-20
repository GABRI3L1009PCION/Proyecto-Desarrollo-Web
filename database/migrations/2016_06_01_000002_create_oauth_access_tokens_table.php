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
        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();

            // user_id puede ser null (tokens de client credentials)
            $table->unsignedBigInteger('user_id')->nullable()->index();

            // Mejora: index para acelerar consultas por cliente
            $table->unsignedBigInteger('client_id')->index();

            $table->string('name')->nullable();
            $table->text('scopes')->nullable();

            // Mejora: valor por defecto para evitar estados indefinidos
            $table->boolean('revoked')->default(false);

            $table->timestamps();
            $table->dateTime('expires_at')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_access_tokens');
    }
};
