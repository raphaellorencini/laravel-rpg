<?php

namespace App\Services;

use App\Models\Guilda;
use App\Repositories\GuildaRepository;
use App\Repositories\JogadorRepository;
use App\Strategy\BalanceamentoXPStrategy;
use Illuminate\Support\Facades\DB;

class GuildaService
{
    public function __construct(
        public JogadorRepository $jogadorRepository,
        public GuildaRepository $guildaRepository,
        public BalanceamentoXPStrategy $balanceamentoXPStrategy,
    )
    {
    }

    public function resetDatabase(int $guildaId)
    {
        Guilda::where('id', $guildaId)->update(['xp_total' => 0]);
        DB::table('guilda_jogador')->where('guilda_id', $guildaId)->delete();
    }

    public function balancear(array $jogadoresIds, int $guildaId): array
    {
        $guildas = $this->guildaRepository->findByFields(['id' => [$guildaId]]);
        $jogadores = $this->jogadorRepository->findByFields([
            'confirmado' => true,
            'jogadores.id' => $jogadoresIds
        ])->toArray();

        return $this->balanceamentoXPStrategy->balancear($jogadores, $guildas);
    }
}