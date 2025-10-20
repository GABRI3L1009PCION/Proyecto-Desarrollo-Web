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
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('name');
            $table->string('secret', 100)->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect');

            // Booleanos con default
            $table->boolean('personal_access_client')->default(false);
            $table->boolean('password_client')->default(false);
            $table->boolean('revoked')->default(false);

            $table->timestamps();

            // Foreign key opcional a users
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('oauth_clients');
    }
};
