<?php

namespace App\Strategy;

use App\Models\Guilda;
use Illuminate\Support\Collection;

interface BalanceamentoInterface
{
    public function balancear(array $jogadores, array|Guilda|Collection $guildas): array;
}