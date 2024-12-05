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
        Schema::create('guilda_jogador', function (Blueprint $table) {
            $table->foreignId('guilda_id');
            $table->foreignId('jogador_id');

            if (Schema::hasTable('guildas') && Schema::hasTable('jogadores')) {
                $table->foreign('guilda_id')->references('id')->on('guildas')->onDelete('cascade');
                $table->foreign('jogador_id')->references('id')->on('jogadores')->onDelete('cascade');
            }

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guilda_jogador');
    }
};
