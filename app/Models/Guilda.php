<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guilda extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nome',
        'maximo_jogadores',
        'xp_total',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jogadores(): BelongsToMany {
        return $this->belongsToMany(Jogador::class, 'guilda_jogador');
    }

    public function sessoes(): BelongsToMany {
        return $this->belongsToMany(Sessao::class, 'sessao_guilda');
    }

    public function adicionarJogador(array $jogador): bool
    {
        // Verifica se a guilda já atingiu o número máximo de jogadores
        if ($this->jogadores()->count() >= $this->maximo_jogadores) {
            return false; // Não pode adicionar mais jogadores
        }

        // Adiciona o jogador à guilda através do relacionamento
        $this->jogadores()->attach($jogador['id']);

        // Atualiza o total de XP da guilda
        $this->increment('xp_total', $jogador['xp']);

        return true;
    }
}
