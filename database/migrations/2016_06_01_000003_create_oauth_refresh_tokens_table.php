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
        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();

            // Enlazado al access token
            $table->string('access_token_id', 100)->index();

            // Evitar estados indefinidos
            $table->boolean('revoked')->default(false);

            $table->dateTime('expires_at')->nullable();

            // Foreign key directa a oauth_access_tokens
            $table->foreign('access_token_id')
                ->references('id')->on('oauth_access_tokens')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_refresh_tokens', function (Blueprint $table) {
            $table->dropForeign(['access_token_id']);
        });

        Schema::dropIfExists('oauth_refresh_tokens');
    }
};
