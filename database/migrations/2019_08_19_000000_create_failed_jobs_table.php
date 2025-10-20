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
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');

            // Cambiado de text a string(191) para permitir índice
            $table->string('queue', 191);

            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();

            // Índice válido en queue
            $table->index('queue', 'failed_jobs_queue_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
    }
};
