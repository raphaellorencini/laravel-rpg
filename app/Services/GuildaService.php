<?php

namespace App\Services;

use App\Repositories\GuildaRepository;

class GuildaService
{
    protected $guildaRepository;

    public function __construct(GuildaRepository $guildaRepository) {
        $this->guildaRepository = $guildaRepository;
    }

    public function distribuirJogadores() {
        $jogadoresConfirmados = $this->guildaRepository->getConfirmados();
        return $this->balancearGuildas($jogadoresConfirmados);
    }

    private function balancearGuildas($jogadores) {
    }
}