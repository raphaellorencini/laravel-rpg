<?php

namespace App\Services;

use App\Models\Guilda;
use App\Repositories\GuildaRepository;
use App\Strategy\BalanceamentoXPStrategy;
use App\Models\Jogador;
use Illuminate\Support\Facades\Auth;

class GuildaService
{
    public function distribuirGuildas()
    {
        // Pega as guildas criadas pelo usuário autenticado
        $guildas = Guilda::where('user_id', Auth::id())->get();

        // Verifica se há guildas suficientes
        $numGuildas = $guildas->count();
        if ($numGuildas < 2) {
            return response()->json(['error' => 'Você precisa criar pelo menos duas guildas.'], 400);
        }

        // Pega os jogadores confirmados
        $jogadores = Jogador::where('confirmado', true)->with('classe')->get()->toArray();

        // Balanceamento usando a estratégia
        $strategy = new BalanceamentoXPStrategy();
        $guildasDistribuidas = $strategy->balancear($jogadores, $numGuildas);

        // Salva a distribuição nas guildas existentes
        foreach ($guildasDistribuidas as $index => $guilda) {
            $guildaExistente = $guildas[$index]; // Pega a guilda existente

            // Remove os jogadores anteriores, se houver, para evitar duplicação
            $guildaExistente->jogadores()->detach();

            // Adiciona os novos jogadores
            $guildaExistente->jogadores()->attach(collect($guilda['jogadores'])->pluck('id'));

            // Atualiza o XP total da guilda
            $guildaExistente->xp_total = collect($guilda['jogadores'])->sum('xp');
            $guildaExistente->save();
        }
    }
}