<?php

namespace App\Repositories;

use App\Models\Guilda;
use App\Models\User;

class GuildaRepository
{
    public function getConfirmados() {
        return User::where('confirmado', true)->get();
    }

    public function criarGuilda($dados): Guilda {
        return Guilda::create($dados);
    }
}