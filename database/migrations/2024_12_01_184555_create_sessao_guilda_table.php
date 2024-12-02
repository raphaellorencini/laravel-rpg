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
        Schema::create('sessao_guilda', function (Blueprint $table) {
            $table->foreignId('sessao_id');
            $table->foreignId('guilda_id');

            if (Schema::hasTable('sessoes') && Schema::hasTable('guildas')) {
                $table->foreign('sessao_id')->references('id')->on('sessoes')->onDelete('cascade');
                $table->foreign('guilda_id')->references('id')->on('guildas')->onDelete('cascade');
            }

            $table->unique(['sessao_id', 'guilda_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessao_guilda');
    }
};
